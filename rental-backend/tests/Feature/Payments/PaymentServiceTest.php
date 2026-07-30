<?php

namespace Tests\Feature\Payments;

use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\VerificationTier;
use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RentalTestSupport;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase, RentalTestSupport;

    private PaymentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
        $this->service = app(PaymentService::class);
    }

    // ── createOrGetBillingForPeriod ───────────────────────────────────────────

    public function test_create_or_get_billing_is_idempotent_for_same_period(): void
    {
        $landlord = $this->makeLandlord();
        $tenant   = $this->makeTenant();
        $property = $this->makeProperty($landlord);
        $lease    = $this->makeLeaseAgreement($tenant, $property);
        $period   = Carbon::now()->startOfMonth();

        $first  = $this->service->createOrGetBillingForPeriod($lease, $period);
        $second = $this->service->createOrGetBillingForPeriod($lease, $period);

        $this->assertEquals($first->id, $second->id);
        $this->assertDatabaseCount('billing', 1);
    }

    public function test_create_or_get_billing_creates_distinct_rows_for_different_months(): void
    {
        $landlord = $this->makeLandlord();
        $tenant   = $this->makeTenant();
        $property = $this->makeProperty($landlord);
        $lease    = $this->makeLeaseAgreement($tenant, $property);

        $january  = Carbon::create(2025, 1, 1);
        $february = Carbon::create(2025, 2, 1);

        $this->service->createOrGetBillingForPeriod($lease, $january);
        $this->service->createOrGetBillingForPeriod($lease, $february);

        $this->assertDatabaseCount('billing', 2);
    }

    // ── initiateRentPayment ───────────────────────────────────────────────────

    public function test_rent_payment_creates_transaction_with_correct_amount_and_user(): void
    {
        $landlord    = $this->makeLandlord();
        $tenant      = $this->makeTenant();
        $property    = $this->makeProperty($landlord, ['price' => 3500]);
        $application = \App\Models\PropertyApplication::create([
            'property_id' => $property->id,
            'user_id'     => $tenant->id,
            'status'      => 'payment_requested',
            'message'     => 'I am interested.',
        ]);

        $txn = $this->service->initiateRentPayment($application, $this->mobileMoneyPayload());

        $this->assertEquals('COMPLETED', $txn->Status);
        $this->assertEquals(3500, $txn->Amount);
        $this->assertEquals($tenant->id, $txn->user_id);
    }

    public function test_rent_payment_transaction_data_contains_no_full_phone(): void
    {
        $landlord    = $this->makeLandlord();
        $tenant      = $this->makeTenant();
        $property    = $this->makeProperty($landlord);
        $application = \App\Models\PropertyApplication::create([
            'property_id' => $property->id,
            'user_id'     => $tenant->id,
            'status'      => 'payment_requested',
            'message'     => 'Interested.',
        ]);

        $txn  = $this->service->initiateRentPayment($application, $this->mobileMoneyPayload('mtn', '0971234567'));
        $data = json_decode($txn->Data, true);

        $this->assertArrayNotHasKey('phone', $data);
        $this->assertArrayHasKey('phone_last4', $data);
        $this->assertEquals(4, strlen($data['phone_last4']));
    }

    // ── initiateSubscriptionPayment ───────────────────────────────────────────

    public function test_subscription_payment_creates_transaction_and_active_subscription(): void
    {
        $user = $this->makeLandlord();
        $tier = VerificationTier::create([
            'name'           => 'professional',
            'tier_type'      => 'landlord',
            'display_name'   => 'Professional',
            'price_display'  => 'K500/mo',
            'price_amount'   => 500,
            'property_limit' => 10,
            'is_active'      => true,
        ]);

        $txn = $this->service->initiateSubscriptionPayment($user, $tier, 'monthly', $this->cardPayload());

        $this->assertEquals('COMPLETED', $txn->Status);
        $this->assertEquals(500, $txn->Amount);
        $this->assertDatabaseHas('subscriptions', [
            'user_id'              => $user->id,
            'verification_tier_id' => $tier->id,
            'status'               => 'active',
        ]);
    }

    public function test_subscription_payment_cancels_prior_active_subscription_for_same_tier_type(): void
    {
        $user = $this->makeLandlord();

        $oldTier = VerificationTier::create([
            'name'           => 'basic_land',
            'tier_type'      => 'landlord',
            'display_name'   => 'Basic',
            'price_display'  => 'Free',
            'price_amount'   => 0,
            'property_limit' => 2,
            'is_active'      => true,
        ]);

        Subscription::create([
            'user_id'              => $user->id,
            'verification_tier_id' => $oldTier->id,
            'status'               => 'active',
            'billing_cycle'        => 'monthly',
            'starts_at'            => now()->subMonth(),
            'ends_at'              => now()->addMonths(11),
        ]);

        $newTier = VerificationTier::create([
            'name'           => 'premium_land',
            'tier_type'      => 'landlord',
            'display_name'   => 'Premium',
            'price_display'  => 'K800/mo',
            'price_amount'   => 800,
            'property_limit' => 50,
            'is_active'      => true,
        ]);

        $this->service->initiateSubscriptionPayment($user, $newTier, 'monthly', $this->cardPayload());

        $this->assertDatabaseHas('subscriptions', [
            'user_id'              => $user->id,
            'verification_tier_id' => $oldTier->id,
            'status'               => 'cancelled',
        ]);
        $this->assertDatabaseHas('subscriptions', [
            'user_id'              => $user->id,
            'verification_tier_id' => $newTier->id,
            'status'               => 'active',
        ]);
    }

    // ── initiateBillingPayment ────────────────────────────────────────────────

    public function test_billing_payment_happy_path_sets_paid_status(): void
    {
        $landlord = $this->makeLandlord();
        $tenant   = $this->makeTenant();
        $property = $this->makeProperty($landlord);
        $lease    = $this->makeLeaseAgreement($tenant, $property);
        $billing  = $this->makeBilling($lease);

        $txn = $this->service->initiateBillingPayment($billing, $this->mobileMoneyPayload());

        $billing->refresh();
        $this->assertEquals('paid', $billing->status);
        $this->assertEquals('COMPLETED', $txn->Status);
    }

    public function test_billing_payment_throws_for_already_paid_bill(): void
    {
        $landlord = $this->makeLandlord();
        $tenant   = $this->makeTenant();
        $property = $this->makeProperty($landlord);
        $lease    = $this->makeLeaseAgreement($tenant, $property);
        $billing  = $this->makeBilling($lease, ['status' => 'paid']);

        $this->expectException(\RuntimeException::class);
        $this->service->initiateBillingPayment($billing, $this->mobileMoneyPayload());
    }
}
