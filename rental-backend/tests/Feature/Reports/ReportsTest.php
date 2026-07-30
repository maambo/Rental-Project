<?php

namespace Tests\Feature\Reports;

use App\Models\Billing;
use App\Models\JobBooking;
use App\Models\LeaseAgreement;
use App\Models\Transaction;
use App\Models\TradeCategory;
use App\Models\WorkerProfile;
use App\Models\WorkerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RentalTestSupport;

class ReportsTest extends TestCase
{
    use RefreshDatabase, RentalTestSupport;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
    }

    public function test_admin_can_view_platform_financial_report(): void
    {
        $admin = $this->makeAdmin();
        $tenant = $this->makeTenant();

        Transaction::create([
            'UID' => 'u1', 'RequestID' => 'r1', 'TransactionID' => 'TXN-AAA111',
            'UserID' => (string) $tenant->id, 'user_id' => $tenant->id,
            'TransactionDate' => now()->toDateString(), 'Amount' => 500,
            'Name' => $tenant->name, 'Type' => 'CARD', 'Status' => 'COMPLETED',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.financial-reports.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Reports/Admin/Index')
            ->where('totalRevenue', 500)
        );
    }

    public function test_admin_can_drill_into_a_revenue_period(): void
    {
        $admin = $this->makeAdmin();
        $tenant = $this->makeTenant();

        Transaction::create([
            'UID' => 'u1', 'RequestID' => 'r1', 'TransactionID' => 'TXN-BBB222',
            'UserID' => (string) $tenant->id, 'user_id' => $tenant->id,
            'TransactionDate' => now()->toDateString(), 'Amount' => 250,
            'Name' => $tenant->name, 'Type' => 'MOBILE_MONEY', 'Status' => 'COMPLETED',
        ]);

        $period = now()->format('Y-m');

        $response = $this->actingAs($admin)
            ->get(route('admin.financial-reports.index', ['period' => $period]));

        $response->assertInertia(fn ($page) => $page
            ->where('drillPeriod', $period)
            ->has('periodDetail', 1)
            ->where('periodDetail.0.amount', 250)
        );
    }

    public function test_landlord_only_sees_their_own_property_income(): void
    {
        $landlordA = $this->makeLandlord();
        $landlordB = $this->makeLandlord();
        $tenant = $this->makeTenant();

        // makeProperty() creates its own Province/District/Town internally, so calling it
        // twice would collide on the hardcoded location code — build the second property
        // directly, reusing the first property's location.
        $propertyA = $this->makeProperty($landlordA, ['title' => 'Property A']);
        $propertyB = \App\Models\Property::create(array_merge(
            $propertyA->only(['province_id', 'district_id', 'town_id', 'street_address', 'property_type', 'property_subtype', 'listing_type', 'price', 'bedrooms', 'bathrooms', 'approval_status', 'availability_status', 'is_visible_in_search', 'is_auto_suspended']),
            ['landlord_id' => $landlordB->id, 'title' => 'Property B', 'description' => 'Second test property.'],
        ));

        $leaseA = LeaseAgreement::create([
            'property_id' => $propertyA->id, 'user_id' => $tenant->id, 'landlord_id' => $landlordA->id,
            'status' => 'active', 'monthly_rent' => 2500,
        ]);
        $leaseB = LeaseAgreement::create([
            'property_id' => $propertyB->id, 'user_id' => $tenant->id, 'landlord_id' => $landlordB->id,
            'status' => 'active', 'monthly_rent' => 3000,
        ]);

        Billing::create([
            'UserID' => (string) $tenant->id, 'Amount' => 2500, 'Date' => now(),
            'Description' => 'Rent', 'Year' => now()->year, 'billing_period' => now()->startOfMonth(),
            'lease_agreement_id' => $leaseA->id, 'status' => 'paid',
        ]);
        Billing::create([
            'UserID' => (string) $tenant->id, 'Amount' => 3000, 'Date' => now(),
            'Description' => 'Rent', 'Year' => now()->year, 'billing_period' => now()->startOfMonth(),
            'lease_agreement_id' => $leaseB->id, 'status' => 'paid',
        ]);

        $response = $this->actingAs($landlordA)->get(route('landlord.reports.index'));

        $response->assertInertia(fn ($page) => $page
            ->component('Reports/Landlord/Index')
            ->has('income', 1)
            ->where('income.0.property', 'Property A')
        );
    }

    public function test_landlord_can_drill_into_a_property_for_bill_detail(): void
    {
        $landlord = $this->makeLandlord();
        $tenant = $this->makeTenant();
        $property = $this->makeProperty($landlord, ['title' => 'Drill Property']);

        $lease = LeaseAgreement::create([
            'property_id' => $property->id, 'user_id' => $tenant->id, 'landlord_id' => $landlord->id,
            'status' => 'active', 'monthly_rent' => 1800,
        ]);

        Billing::create([
            'UserID' => (string) $tenant->id, 'Amount' => 1800, 'Date' => now(),
            'Description' => 'Rent for this month', 'Year' => now()->year, 'billing_period' => now()->startOfMonth(),
            'lease_agreement_id' => $lease->id, 'status' => 'paid',
        ]);

        $response = $this->actingAs($landlord)
            ->get(route('landlord.reports.index', ['property' => $property->id]));

        $response->assertInertia(fn ($page) => $page
            ->where('drillPropertyId', $property->id)
            ->has('propertyDetail', 1)
            ->where('propertyDetail.0.amount', 1800)
        );
    }

    public function test_tenant_only_sees_their_own_payment_history(): void
    {
        $landlord = $this->makeLandlord();
        $tenant1 = $this->makeTenant();
        $tenant2 = $this->makeTenant();
        $property = $this->makeProperty($landlord);

        $lease1 = LeaseAgreement::create([
            'property_id' => $property->id, 'user_id' => $tenant1->id, 'landlord_id' => $landlord->id,
            'status' => 'active', 'monthly_rent' => 1000,
        ]);

        Billing::create([
            'UserID' => (string) $tenant1->id, 'Amount' => 1000, 'Date' => now(),
            'Description' => 'Rent', 'Year' => now()->year, 'billing_period' => now()->startOfMonth(),
            'lease_agreement_id' => $lease1->id, 'status' => 'paid',
        ]);
        Billing::create([
            'UserID' => (string) $tenant2->id, 'Amount' => 999, 'Date' => now(),
            'Description' => 'Someone else\'s rent', 'Year' => now()->year, 'billing_period' => now()->startOfMonth(),
        ]);

        $response = $this->actingAs($tenant1)->get(route('tenant.reports.index'));

        $response->assertInertia(fn ($page) => $page
            ->component('Reports/Tenant/Index')
            ->has('history', 1)
            ->where('history.0.amount', 1000)
        );
    }

    public function test_worker_can_drill_into_earnings_by_service(): void
    {
        $worker = $this->makeTenant(); // any authenticated user can hold a worker profile
        $client = $this->makeTenant();
        $category = TradeCategory::create(['name' => 'Plumbing', 'slug' => 'plumbing', 'is_active' => true, 'sort_order' => 1]);

        $profile = WorkerProfile::create([
            'user_id' => $worker->id,
            'trade_category_id' => $category->id,
            'bio' => 'Test bio',
            'experience_years' => 5,
            'phone' => '0966000000',
            'is_active' => true,
        ]);

        $service = WorkerService::create([
            'worker_profile_id' => $profile->id,
            'service_name' => 'Pipe Repair',
            'rate_type' => 'per_job',
            'base_rate' => 300,
        ]);

        JobBooking::create([
            'client_id' => $client->id,
            'worker_profile_id' => $profile->id,
            'worker_service_id' => $service->id,
            'job_description' => 'Fix leaking pipe',
            'status' => 'completed',
            'agreed_price' => 300,
            'platform_fee' => 24,
            'worker_net' => 276,
            'completed_at' => now(),
        ]);

        $response = $this->actingAs($worker)
            ->get(route('worker.reports.index', ['service' => 'Pipe Repair']));

        $response->assertInertia(fn ($page) => $page
            ->component('Reports/Worker/Index')
            ->has('income', 1)
            ->where('income.0.net_earnings', 276)
            ->where('drillService', 'Pipe Repair')
            ->has('serviceDetail', 1)
            ->where('serviceDetail.0.worker_net', 276)
        );
    }
}
