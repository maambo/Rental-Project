<?php

namespace Tests\Feature\Reports;

use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\VerificationTier;
use App\Services\ReportingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tests\Traits\RentalTestSupport;

class ReportingServiceTest extends TestCase
{
    use RefreshDatabase, RentalTestSupport;

    private ReportingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
        $this->service = app(ReportingService::class);
    }

    private function seedTransaction(array $overrides = []): Transaction
    {
        $createdAt = $overrides['created_at'] ?? null;
        $data      = array_merge([
            'UID'             => (string) Str::uuid(),
            'RequestID'       => (string) Str::uuid(),
            'TransactionID'   => 'TXN-' . strtoupper(bin2hex(random_bytes(4))),
            'UserID'          => '1',
            'TransactionDate' => now()->toDateString(),
            'Amount'          => 1000,
            'Name'            => 'Test User',
            'Type'            => 'MOBILE_MONEY',
            'Status'          => 'COMPLETED',
            'Data'            => json_encode(['provider' => 'mtn', 'phone_last4' => '1234']),
        ], Arr::except($overrides, ['created_at']));

        $txn = Transaction::create($data);

        // Eloquent overwrites created_at on insert, so set it via a raw update.
        if ($createdAt !== null) {
            DB::table('transactions')
                ->where('id', $txn->id)
                ->update(['created_at' => $createdAt, 'updated_at' => $createdAt]);
            $txn->created_at = $createdAt;
        }

        return $txn;
    }

    // ── totalRevenue / averageTransaction ─────────────────────────────────────

    public function test_total_revenue_sums_only_completed_transactions(): void
    {
        $this->seedTransaction(['Amount' => 500]);
        $this->seedTransaction(['Amount' => 300]);
        $this->seedTransaction(['Amount' => 200, 'Status' => 'PENDING']);

        $this->assertEquals(800.0, $this->service->totalRevenue());
    }

    public function test_average_transaction_is_correct(): void
    {
        $this->seedTransaction(['Amount' => 400]);
        $this->seedTransaction(['Amount' => 600]);

        $this->assertEquals(500.0, $this->service->averageTransaction());
    }

    // ── revenueByPeriod ───────────────────────────────────────────────────────

    public function test_revenue_by_period_groups_correctly_by_month(): void
    {
        $jan = Carbon::create(2025, 1, 15);
        $feb = Carbon::create(2025, 2, 10);

        $this->seedTransaction(['Amount' => 1000, 'created_at' => $jan]);
        $this->seedTransaction(['Amount' => 2000, 'created_at' => $feb]);

        $result = $this->service->revenueByPeriod(
            Carbon::create(2025, 1, 1),
            Carbon::create(2025, 2, 28)
        );

        $this->assertCount(2, $result);
        $jan_entry = $result->firstWhere('period', '2025-01');
        $this->assertEquals(1000.0, $jan_entry['total']);
    }

    // ── landlordIncomeBreakdown ───────────────────────────────────────────────

    public function test_landlord_a_does_not_see_landlord_b_billing(): void
    {
        $landlordA = $this->makeLandlord();
        $landlordB = $this->makeLandlord();
        $tenant    = $this->makeTenant();

        $propertyA = $this->makeProperty($landlordA);
        $propertyB = $this->makeProperty($landlordB);
        $leaseA    = $this->makeLeaseAgreement($tenant, $propertyA);
        $leaseB    = $this->makeLeaseAgreement($tenant, $propertyB);

        $this->makeBilling($leaseA, ['Amount' => 2500]);
        $this->makeBilling($leaseB, ['Amount' => 3000]);

        $from = now()->subMonth();
        $to   = now()->addMonth();

        $resultA = $this->service->landlordIncomeBreakdown($landlordA, $from, $to);
        $resultB = $this->service->landlordIncomeBreakdown($landlordB, $from, $to);

        $this->assertCount(1, $resultA);
        $this->assertEquals(2500.0, $resultA->first()['total_billed']);

        $this->assertCount(1, $resultB);
        $this->assertEquals(3000.0, $resultB->first()['total_billed']);
    }

    // ── landlordPropertyBillingDetail ────────────────────────────────────────

    public function test_landlord_property_billing_detail_filters_by_property(): void
    {
        $landlord  = $this->makeLandlord();
        $tenant    = $this->makeTenant();
        $propertyA = $this->makeProperty($landlord);
        $propertyB = $this->makeProperty($landlord);
        $leaseA    = $this->makeLeaseAgreement($tenant, $propertyA);
        $leaseB    = $this->makeLeaseAgreement($tenant, $propertyB);
        $this->makeBilling($leaseA, ['Amount' => 2000]);
        $this->makeBilling($leaseB, ['Amount' => 4000]);

        $from = now()->subMonth();
        $to   = now()->addMonth();

        $detail = $this->service->landlordPropertyBillingDetail($landlord, $propertyA->id, $from, $to);

        $this->assertCount(1, $detail);
        $this->assertEquals(2000.0, $detail->first()['amount']);
    }

    // ── tenantPaymentHistory ─────────────────────────────────────────────────

    public function test_tenant_a_does_not_see_tenant_b_history(): void
    {
        $landlord = $this->makeLandlord();
        $tenantA  = $this->makeTenant();
        $tenantB  = $this->makeTenant();

        $propA  = $this->makeProperty($landlord);
        $propB  = $this->makeProperty($landlord);
        $leaseA = $this->makeLeaseAgreement($tenantA, $propA);
        $leaseB = $this->makeLeaseAgreement($tenantB, $propB);
        $this->makeBilling($leaseA, ['Amount' => 1500]);
        $this->makeBilling($leaseB, ['Amount' => 3000]);

        $from = now()->subMonth();
        $to   = now()->addMonth();

        $historyA = $this->service->tenantPaymentHistory($tenantA, $from, $to);
        $historyB = $this->service->tenantPaymentHistory($tenantB, $from, $to);

        $this->assertCount(1, $historyA);
        $this->assertEquals(1500.0, $historyA->first()['amount']);

        $this->assertCount(1, $historyB);
        $this->assertEquals(3000.0, $historyB->first()['amount']);
    }

    // ── outstandingBalances ───────────────────────────────────────────────────

    public function test_outstanding_balances_returns_pending_billings(): void
    {
        $landlord = $this->makeLandlord();
        $tenant   = $this->makeTenant();
        $property = $this->makeProperty($landlord);
        $lease    = $this->makeLeaseAgreement($tenant, $property);

        $this->makeBilling($lease, ['status' => 'pending', 'Amount' => 2000]);
        $this->makeBilling($lease, [
            'status'         => 'paid',
            'Amount'         => 2000,
            'billing_period' => now()->subMonth()->startOfMonth()->toDateString(),
        ]);

        $result = $this->service->outstandingBalances();

        $this->assertCount(1, $result);
    }

    // ── subscriptionRevenue ───────────────────────────────────────────────────

    public function test_subscription_revenue_sums_by_tier_type(): void
    {
        $tier = VerificationTier::create([
            'name'           => 'sub_test',
            'tier_type'      => 'landlord',
            'display_name'   => 'Sub Test',
            'price_display'  => 'K200/mo',
            'price_amount'   => 200,
            'property_limit' => 5,
            'is_active'      => true,
        ]);
        $user = $this->makeLandlord();

        Subscription::create([
            'user_id'              => $user->id,
            'verification_tier_id' => $tier->id,
            'status'               => 'active',
            'billing_cycle'        => 'monthly',
            'starts_at'            => now(),
            'ends_at'              => now()->addMonth(),
        ]);

        $from   = now()->subDay();
        $to     = now()->addDay();
        $result = $this->service->subscriptionRevenue($from, $to);

        $this->assertCount(1, $result);
        $landlordEntry = $result->firstWhere('tier_type', 'landlord');
        $this->assertEquals(200.0, $landlordEntry['total']);
    }

    // ── workerIncomeBreakdown ─────────────────────────────────────────────────

    public function test_worker_a_income_does_not_include_worker_b_earnings(): void
    {
        $workerUserA = $this->makeTenant();
        $workerUserB = $this->makeTenant();
        $client      = $this->makeTenant();

        $profileA = $this->makeWorkerProfile($workerUserA);
        $profileB = $this->makeWorkerProfile($workerUserB);
        $serviceA = $this->makeWorkerService($profileA);
        $serviceB = $this->makeWorkerService($profileB);

        $this->makeJobBooking($client, $profileA, $serviceA, [
            'status'       => 'completed',
            'agreed_price' => 1000,
            'platform_fee' => 80,
            'worker_net'   => 920,
            'completed_at' => now(),
        ]);
        $this->makeJobBooking($client, $profileB, $serviceB, [
            'status'       => 'completed',
            'agreed_price' => 2000,
            'platform_fee' => 160,
            'worker_net'   => 1840,
            'completed_at' => now(),
        ]);

        $from = now()->subDay();
        $to   = now()->addDay();

        $breakdownA = $this->service->workerIncomeBreakdown($workerUserA, $from, $to);
        $breakdownB = $this->service->workerIncomeBreakdown($workerUserB, $from, $to);

        $this->assertEquals(920.0, $breakdownA->sum('net_earnings'));
        $this->assertEquals(1840.0, $breakdownB->sum('net_earnings'));
    }

    // ── workerServiceBookingDetail ────────────────────────────────────────────

    public function test_worker_service_booking_detail_filters_by_service_name(): void
    {
        $workerUser = $this->makeTenant();
        $client     = $this->makeTenant();
        $profile    = $this->makeWorkerProfile($workerUser);
        $serviceA   = $this->makeWorkerService($profile, ['service_name' => 'Pipe Repair']);
        $serviceB   = $this->makeWorkerService($profile, ['service_name' => 'Tile Work']);

        $this->makeJobBooking($client, $profile, $serviceA, [
            'status'       => 'completed',
            'agreed_price' => 500,
            'completed_at' => now(),
        ]);
        $this->makeJobBooking($client, $profile, $serviceB, [
            'status'       => 'completed',
            'agreed_price' => 800,
            'completed_at' => now(),
        ]);

        $from   = now()->subDay();
        $to     = now()->addDay();
        $detail = $this->service->workerServiceBookingDetail($workerUser, 'Pipe Repair', $from, $to);

        $this->assertCount(1, $detail);
        $this->assertEquals(500.0, $detail->first()['agreed_price']);
    }
}
