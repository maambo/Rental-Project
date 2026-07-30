<?php

namespace Tests\Traits;

use App\Models\Billing;
use App\Models\District;
use App\Models\JobBooking;
use App\Models\LandlordApplication;
use App\Models\LeaseAgreement;
use App\Models\Property;
use App\Models\Province;
use App\Models\Role;
use App\Models\Town;
use App\Models\TradeCategory;
use App\Models\User;
use App\Models\VerificationTier;
use App\Models\WorkerProfile;
use App\Models\WorkerService;

trait RentalTestSupport
{
    protected Role $adminRole;
    protected Role $landlordRole;
    protected Role $tenantRole;
    protected Role $applicantRole;

    private static int $locationSeq = 0;

    protected function createRoles(): void
    {
        $this->adminRole     = Role::create(['name' => 'admin',              'display_name' => 'Administrator',      'description' => '']);
        $this->landlordRole  = Role::create(['name' => 'landlord',           'display_name' => 'Landlord',           'description' => '']);
        $this->tenantRole    = Role::create(['name' => 'tenant',             'display_name' => 'Tenant',             'description' => '']);
        $this->applicantRole = Role::create(['name' => 'applicant_landlord', 'display_name' => 'Applicant Landlord', 'description' => '']);

        // Seed a starter tier so promoteToLandlord() can provision a subscription
        VerificationTier::firstOrCreate(
            ['name' => 'starter', 'tier_type' => 'landlord'],
            [
                'display_name'   => 'Starter',
                'price_display'  => 'Free',
                'price_amount'   => 0,
                'property_limit' => 1,
                'is_active'      => true,
            ]
        );
    }

    protected function makeAdmin(): User
    {
        return User::factory()->create(['role_id' => $this->adminRole->id]);
    }

    protected function makeLandlord(): User
    {
        return User::factory()->create(['role_id' => $this->landlordRole->id]);
    }

    protected function makeTenant(): User
    {
        return User::factory()->create(['role_id' => $this->tenantRole->id]);
    }

    protected function makeApplicant(): User
    {
        return User::factory()->create(['role_id' => $this->applicantRole->id]);
    }

    /**
     * Returns [Province, District, Town]. Each call creates a unique Province code
     * to avoid UniqueConstraintViolationException when called multiple times per test.
     */
    protected function makeLocation(): array
    {
        $seq = ++self::$locationSeq;
        $province = Province::firstOrCreate(
            ['code' => 'LS' . $seq],
            ['name' => 'Lusaka ' . $seq]
        );
        $district = District::create(['name' => 'Lusaka ' . $seq, 'province_id' => $province->id]);
        $town     = Town::create(['name' => 'Lusaka ' . $seq, 'district_id' => $district->id]);
        return [$province, $district, $town];
    }

    /**
     * Create a Property record directly (bypasses controller).
     * Defaults to approved + visible + available so tests can apply to it right away.
     */
    protected function makeProperty(User $landlord, array $overrides = []): Property
    {
        [$province, $district, $town] = $this->makeLocation();

        return Property::create(array_merge([
            'landlord_id'          => $landlord->id,
            'province_id'          => $province->id,
            'district_id'          => $district->id,
            'town_id'              => $town->id,
            'street_address'       => '123 Test Street',
            'property_type'        => 'residential',
            'property_subtype'     => 'apartment',
            'listing_type'         => 'rent',
            'title'                => 'Test Property',
            'description'          => 'A test property.',
            'price'                => 2500,
            'bedrooms'             => 2,
            'bathrooms'            => 1,
            'approval_status'      => 'approved',
            'availability_status'  => 'available',
            'is_visible_in_search' => true,
            'is_auto_suspended'    => false,
        ], $overrides));
    }

    /** Create a LandlordApplication record directly. */
    protected function makeLandlordApplication(User $user, array $overrides = []): LandlordApplication
    {
        return LandlordApplication::create(array_merge([
            'user_id'              => $user->id,
            'status'               => 'pending',
            'nrc_passport'         => fake()->unique()->numerify('######/##/#'),
            'address'              => '123 Test St',
            'province'             => 'Lusaka',
            'town'                 => 'Lusaka',
            'landlord_type'        => 'private_landlord',
            'id_document_url'      => 'docs/id.jpg',
            'proof_of_address_url' => 'docs/proof.jpg',
            'selfie_url'           => 'docs/selfie.jpg',
        ], $overrides));
    }

    /** Create a LeaseAgreement record directly. */
    protected function makeLeaseAgreement(User $tenant, Property $property, array $overrides = []): LeaseAgreement
    {
        return LeaseAgreement::create(array_merge([
            'property_id'  => $property->id,
            'user_id'      => $tenant->id,
            'landlord_id'  => $property->landlord_id,
            'status'       => 'active',
            'content'      => 'Test lease agreement.',
            'monthly_rent' => 2500,
            'start_date'   => now()->startOfMonth(),
            'end_date'     => now()->addYear()->endOfMonth(),
        ], $overrides));
    }

    /** Create a Billing record directly for a given lease. */
    protected function makeBilling(LeaseAgreement $lease, array $overrides = []): Billing
    {
        $period = now()->startOfMonth();
        return Billing::create(array_merge([
            'lease_agreement_id' => $lease->id,
            'UserID'             => (string) $lease->user_id,
            'Amount'             => $lease->monthly_rent,
            'Date'               => $period,
            'Description'        => 'Rent for ' . $period->format('F Y'),
            'Year'               => $period->year,
            'billing_period'     => $period->toDateTimeString(),
            'status'             => 'pending',
        ], $overrides));
    }

    /** Create a WorkerProfile record directly (active + verified). */
    protected function makeWorkerProfile(User $user, array $overrides = []): WorkerProfile
    {
        $category = TradeCategory::firstOrCreate(
            ['slug' => 'plumbing'],
            ['name' => 'Plumbing', 'icon' => '🔧', 'is_active' => true, 'sort_order' => 1]
        );
        [, , $town] = $this->makeLocation();

        return WorkerProfile::create(array_merge([
            'user_id'           => $user->id,
            'trade_category_id' => $category->id,
            'town_id'           => $town->id,
            'tagline'           => 'Test worker',
            'bio'               => 'Experienced worker.',
            'experience_years'  => 3,
            'phone'             => '0971234567',
            'is_verified'       => true,
            'is_active'         => true,
            'is_featured'       => false,
            'rating_average'    => 0,
            'rating_count'      => 0,
        ], $overrides));
    }

    /** Create a WorkerService record directly. */
    protected function makeWorkerService(WorkerProfile $profile, array $overrides = []): WorkerService
    {
        return WorkerService::create(array_merge([
            'worker_profile_id' => $profile->id,
            'service_name'      => 'Pipe Repair',
            'description'       => 'Fix leaking pipes.',
            'rate_type'         => 'hourly',
            'base_rate'         => 150,
            'minimum_charge'    => 200,
            'is_active'         => true,
        ], $overrides));
    }

    /** Create a JobBooking record directly. */
    protected function makeJobBooking(User $client, WorkerProfile $worker, WorkerService $service, array $overrides = []): JobBooking
    {
        return JobBooking::create(array_merge([
            'client_id'         => $client->id,
            'worker_profile_id' => $worker->id,
            'worker_service_id' => $service->id,
            'job_description'   => 'Need pipe fixed urgently.',
            'location'          => '123 Test Street',
            'agreed_price'      => 500,
            'platform_fee'      => 40,
            'worker_net'        => 460,
            'status'            => 'pending',
        ], $overrides));
    }

    /** Standard mobile money payload for payment tests. */
    protected function mobileMoneyPayload(string $provider = 'mtn', string $phone = '0971234567'): array
    {
        return ['method' => 'mobile_money', 'provider' => $provider, 'phone' => $phone];
    }

    /** Standard card payload for payment tests. */
    protected function cardPayload(string $number = '4111111111111111'): array
    {
        return [
            'method'          => 'card',
            'card_number'     => $number,
            'card_expiry'     => '12/28',
            'card_cvv'        => '123',
            'cardholder_name' => 'Test User',
        ];
    }
}
