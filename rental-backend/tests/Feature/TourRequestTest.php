<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RentalTestSupport;

class TourRequestTest extends TestCase
{
    use RefreshDatabase, RentalTestSupport;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
    }

    public function test_tenant_can_submit_a_tour_request(): void
    {
        $landlord = $this->makeLandlord();
        $property = $this->makeProperty($landlord);
        $tenant   = $this->makeTenant();

        $this->actingAs($tenant)
            ->post(route('properties.tour.store', $property), [
                'scheduled_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
                'notes'        => 'Please call before arriving.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('tour_requests', [
            'property_id' => $property->id,
            'user_id'     => $tenant->id,
            'status'      => 'pending',
        ]);
    }

    public function test_tour_request_must_be_in_the_future(): void
    {
        $landlord = $this->makeLandlord();
        $property = $this->makeProperty($landlord);
        $tenant   = $this->makeTenant();

        $this->actingAs($tenant)
            ->post(route('properties.tour.store', $property), [
                'scheduled_at' => now()->subDay()->format('Y-m-d H:i:s'),
            ])
            ->assertSessionHasErrors('scheduled_at');
    }

    public function test_tour_request_scheduled_at_is_required(): void
    {
        $landlord = $this->makeLandlord();
        $property = $this->makeProperty($landlord);

        $this->actingAs($this->makeTenant())
            ->post(route('properties.tour.store', $property), [])
            ->assertSessionHasErrors('scheduled_at');
    }

    public function test_unauthenticated_user_cannot_submit_tour_request(): void
    {
        $landlord = $this->makeLandlord();
        $property = $this->makeProperty($landlord);

        $this->post(route('properties.tour.store', $property), [
            'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
        ])->assertRedirect('/login');
    }

    public function test_tour_request_stores_name_and_email_from_user(): void
    {
        $landlord = $this->makeLandlord();
        $property = $this->makeProperty($landlord);
        $tenant   = $this->makeTenant();

        $this->actingAs($tenant)
            ->post(route('properties.tour.store', $property), [
                'scheduled_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
            ]);

        $this->assertDatabaseHas('tour_requests', [
            'property_id' => $property->id,
            'name'        => $tenant->name,
            'email'       => $tenant->email,
        ]);
    }
}
