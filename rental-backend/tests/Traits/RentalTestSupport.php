<?php

namespace Tests\Traits;

use App\Models\District;
use App\Models\LandlordApplication;
use App\Models\Property;
use App\Models\Province;
use App\Models\Role;
use App\Models\Town;
use App\Models\User;
use App\Models\VerificationTier;

trait RentalTestSupport
{
    protected Role $adminRole;
    protected Role $landlordRole;
    protected Role $tenantRole;
    protected Role $applicantRole;

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

    /** Returns [Province, District, Town]. */
    protected function makeLocation(): array
    {
        $province = Province::create(['name' => 'Lusaka', 'code' => 'LS']);
        $district = District::create(['name' => 'Lusaka', 'province_id' => $province->id]);
        $town     = Town::create(['name' => 'Lusaka', 'district_id' => $district->id]);
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
}
