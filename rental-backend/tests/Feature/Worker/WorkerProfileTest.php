<?php

namespace Tests\Feature\Worker;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RentalTestSupport;

class WorkerProfileTest extends TestCase
{
    use RefreshDatabase, RentalTestSupport;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
    }

    public function test_worker_without_profile_is_redirected_to_profile_create_on_dashboard(): void
    {
        $user = $this->makeTenant();

        $this->actingAs($user)
             ->get(route('worker.dashboard'))
             ->assertRedirect(route('worker.profile.create'));
    }

    public function test_worker_with_profile_cannot_access_profile_create(): void
    {
        $user = $this->makeTenant();
        $this->makeWorkerProfile($user);

        $this->actingAs($user)
             ->get(route('worker.profile.create'))
             ->assertRedirect(route('worker.dashboard'));
    }

    public function test_worker_edit_returns_404_when_no_profile(): void
    {
        $user = $this->makeTenant();

        $this->actingAs($user)
             ->get(route('worker.profile.edit'))
             ->assertStatus(404);
    }

    public function test_worker_can_add_a_service(): void
    {
        $user    = $this->makeTenant();
        $profile = $this->makeWorkerProfile($user);

        $this->actingAs($user)
             ->post(route('worker.services.store'), [
                 'service_name'   => 'Electrical Wiring',
                 'description'    => 'Install and repair electrical systems.',
                 'rate_type'      => 'per_job',
                 'base_rate'      => 500,
                 'minimum_charge' => 300,
             ])
             ->assertRedirect();

        $this->assertDatabaseHas('worker_services', [
            'worker_profile_id' => $profile->id,
            'service_name'      => 'Electrical Wiring',
        ]);
    }

    public function test_worker_cannot_delete_another_workers_service(): void
    {
        $userA = $this->makeTenant();
        $userB = $this->makeTenant();

        $profileA = $this->makeWorkerProfile($userA);
        $this->makeWorkerProfile($userB);
        $serviceA = $this->makeWorkerService($profileA);

        $this->actingAs($userB)
             ->delete(route('worker.services.destroy', $serviceA))
             ->assertStatus(403);
    }
}
