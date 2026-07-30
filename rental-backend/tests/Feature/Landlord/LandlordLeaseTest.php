<?php

namespace Tests\Feature\Landlord;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RentalTestSupport;

class LandlordLeaseTest extends TestCase
{
    use RefreshDatabase, RentalTestSupport;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
    }

    public function test_landlord_can_view_lease_create_form_for_their_application(): void
    {
        $landlord    = $this->makeLandlord();
        $tenant      = $this->makeTenant();
        $property    = $this->makeProperty($landlord);

        $application = \App\Models\PropertyApplication::create([
            'property_id'    => $property->id,
            'user_id'        => $tenant->id,
            'status'         => 'under_review',
            'message'        => 'I would like to rent.',
            'move_in_date'   => now()->addMonth()->toDateString(),
            'lease_duration' => 12,
        ]);

        $this->actingAs($landlord)
             ->get(route('landlord.leases.create', ['application_id' => $application->id]))
             ->assertOk();
    }

    public function test_landlord_cannot_view_another_landlords_application(): void
    {
        $landlordA  = $this->makeLandlord();
        $landlordB  = $this->makeLandlord();
        $tenant     = $this->makeTenant();
        $propertyB  = $this->makeProperty($landlordB);

        $application = \App\Models\PropertyApplication::create([
            'property_id'    => $propertyB->id,
            'user_id'        => $tenant->id,
            'status'         => 'under_review',
            'message'        => 'Looking for a place.',
            'move_in_date'   => now()->addMonth()->toDateString(),
            'lease_duration' => 12,
        ]);

        $this->actingAs($landlordA)
             ->get(route('landlord.leases.create', ['application_id' => $application->id]))
             ->assertForbidden();
    }

    public function test_landlord_can_create_lease(): void
    {
        $landlord = $this->makeLandlord();
        $tenant   = $this->makeTenant();
        $property = $this->makeProperty($landlord);

        $this->actingAs($landlord)
             ->post(route('landlord.leases.store'), [
                 'property_id'  => $property->id,
                 'user_id'      => $tenant->id,
                 'monthly_rent' => 2500,
                 'start_date'   => now()->addWeek()->toDateString(),
                 'end_date'     => now()->addYear()->toDateString(),
                 'content'      => 'Standard lease agreement terms apply.',
             ])
             ->assertRedirect(route('landlord.properties.index'));

        $this->assertDatabaseHas('lease_agreements', [
            'property_id'  => $property->id,
            'user_id'      => $tenant->id,
            'landlord_id'  => $landlord->id,
            'monthly_rent' => 2500,
        ]);
    }

    public function test_landlord_can_view_their_own_lease(): void
    {
        $landlord = $this->makeLandlord();
        $tenant   = $this->makeTenant();
        $property = $this->makeProperty($landlord);
        $lease    = $this->makeLeaseAgreement($tenant, $property);

        $this->actingAs($landlord)
             ->get(route('landlord.leases.show', $lease))
             ->assertOk();
    }

    public function test_landlord_cannot_view_another_landlords_lease(): void
    {
        $landlordA = $this->makeLandlord();
        $landlordB = $this->makeLandlord();
        $tenant    = $this->makeTenant();
        $property  = $this->makeProperty($landlordB);
        $lease     = $this->makeLeaseAgreement($tenant, $property);

        $this->actingAs($landlordA)
             ->get(route('landlord.leases.show', $lease))
             ->assertForbidden();
    }
}
