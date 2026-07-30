<?php

namespace App\Services;

use App\Models\Billing;
use App\Models\LeaseAgreement;
use App\Models\PropertyApplication;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Models\VerificationTier;
use App\Services\Payments\PaymentMethodSimulator;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Single entry point for creating payments across the platform — rent/deposit
 * payments, subscription checkouts, and recurring lease billing. Every payment
 * flows through the PaymentMethodSimulator (no live gateway yet); this class
 * is the seam a real gateway integration would sit behind later.
 */
class PaymentService
{
    public function __construct(
        private readonly PaymentMethodSimulator $simulator,
    ) {}

    /**
     * Tenant pays to finalise a property application (rent deposit / first payment).
     */
    public function initiateRentPayment(PropertyApplication $application, array $methodData): Transaction
    {
        $charge = $this->simulator->charge($methodData);
        $property = $application->property;
        $tenant = $application->user;

        return Transaction::create([
            'UID' => (string) Str::uuid(),
            'RequestID' => (string) Str::uuid(),
            'TransactionID' => $this->generateTransactionId(),
            'UserID' => (string) $tenant->id,
            'user_id' => $tenant->id,
            'TransactionDate' => now()->toDateString(),
            'Amount' => $property->price,
            'Name' => $tenant->name,
            'Type' => $charge['type'],
            'Status' => 'COMPLETED',
            'Data' => json_encode(array_merge($charge['display'], [
                'source_type' => 'property_application',
                'source_id' => $application->id,
            ])),
        ]);
    }

    /**
     * User purchases/renews a subscription tier (landlord, tenant, or worker).
     */
    public function initiateSubscriptionPayment(
        User $user,
        VerificationTier $tier,
        string $billingCycle,
        array $methodData,
    ): Transaction {
        return DB::transaction(function () use ($user, $tier, $billingCycle, $methodData) {
            $charge = $this->simulator->charge($methodData);

            $transaction = Transaction::create([
                'UID' => (string) Str::uuid(),
                'RequestID' => (string) Str::uuid(),
                'TransactionID' => $this->generateTransactionId(),
                'UserID' => (string) $user->id,
                'user_id' => $user->id,
                'TransactionDate' => now()->toDateString(),
                'Amount' => $tier->price_amount,
                'Name' => $user->name,
                'Type' => $charge['type'],
                'Status' => 'COMPLETED',
                'Data' => json_encode(array_merge($charge['display'], [
                    'source_type' => 'subscription',
                    'verification_tier_id' => $tier->id,
                ])),
            ]);

            // Cancel any existing active subscription for the same tier type before
            // activating the new one — mirrors Admin\SubscriptionController::store.
            Subscription::where('user_id', $user->id)
                ->whereHas('tier', fn ($q) => $q->where('tier_type', $tier->tier_type))
                ->active()
                ->update(['status' => 'cancelled', 'cancelled_at' => now()]);

            $endsAt = match ($billingCycle) {
                'monthly' => now()->addMonth(),
                'annual' => now()->addYear(),
                default => null,
            };

            Subscription::create([
                'user_id' => $user->id,
                'verification_tier_id' => $tier->id,
                'status' => 'active',
                'billing_cycle' => $billingCycle,
                'starts_at' => now(),
                'ends_at' => $endsAt,
            ]);

            return $transaction;
        });
    }

    /**
     * Tenant pays an existing (recurring) Billing record in-app via a simulated method.
     */
    public function initiateBillingPayment(Billing $billing, array $methodData): Transaction
    {
        if ($billing->status === 'paid') {
            throw new \RuntimeException('This bill has already been paid.');
        }

        return DB::transaction(function () use ($billing, $methodData) {
            $charge = $this->simulator->charge($methodData);
            $tenant = $billing->user;

            $transaction = Transaction::create([
                'UID' => (string) Str::uuid(),
                'RequestID' => (string) Str::uuid(),
                'TransactionID' => $this->generateTransactionId(),
                'UserID' => (string) $billing->UserID,
                'user_id' => $tenant?->id,
                'TransactionDate' => now()->toDateString(),
                'Amount' => $billing->Amount,
                'Name' => $tenant?->name ?? $billing->UserID,
                'Type' => $charge['type'],
                'Status' => 'COMPLETED',
                'Data' => json_encode(array_merge($charge['display'], [
                    'source_type' => 'billing',
                    'source_id' => $billing->id,
                ])),
            ]);

            $billing->update(['status' => 'paid', 'paid_at' => now()]);

            return $transaction;
        });
    }

    /**
     * Get (or create) the single Billing record for a lease's given billing period,
     * relying on the billing_period column added in Phase 0 to allow one bill per
     * lease per month instead of the old one-bill-per-user-per-year limit.
     */
    public function createOrGetBillingForPeriod(LeaseAgreement $lease, CarbonInterface $period): Billing
    {
        $periodStart = $period->copy()->startOfMonth();

        return Billing::firstOrCreate(
            [
                'lease_agreement_id' => $lease->id,
                'billing_period' => $periodStart->toDateTimeString(),
            ],
            [
                'UserID' => (string) $lease->user_id,
                'Amount' => $lease->monthly_rent,
                'Date' => $periodStart,
                'Description' => 'Rent for ' . $periodStart->format('F Y'),
                'Year' => $periodStart->year,
                'status' => 'pending',
            ],
        );
    }

    private function generateTransactionId(): string
    {
        return 'TXN-' . strtoupper(bin2hex(random_bytes(6)));
    }
}
