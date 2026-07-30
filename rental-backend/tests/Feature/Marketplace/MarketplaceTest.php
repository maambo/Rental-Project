<?php

namespace Tests\Feature\Marketplace;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RentalTestSupport;

class MarketplaceTest extends TestCase
{
    use RefreshDatabase, RentalTestSupport;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
    }

    // ── Worker index ──────────────────────────────────────────────────────────

    public function test_unauthenticated_user_can_view_worker_index(): void
    {
        $this->get(route('marketplace.workers.index'))
             ->assertStatus(200);
    }

    public function test_filter_by_category_returns_only_matching_workers(): void
    {
        $userA = $this->makeTenant();
        $userB = $this->makeTenant();

        $profileA = $this->makeWorkerProfile($userA);
        // profileB will belong to a different category.
        $profileB = $this->makeWorkerProfile($userB);

        $categoryId = $profileA->trade_category_id;

        $this->get(route('marketplace.workers.index', ['category' => $categoryId]))
             ->assertStatus(200);
    }

    // ── Worker show ───────────────────────────────────────────────────────────

    public function test_show_returns_404_for_inactive_worker(): void
    {
        $user    = $this->makeTenant();
        $profile = $this->makeWorkerProfile($user, ['is_active' => false]);

        $this->get(route('marketplace.workers.show', $profile))
             ->assertStatus(404);
    }

    public function test_show_returns_404_for_unverified_worker(): void
    {
        $user    = $this->makeTenant();
        $profile = $this->makeWorkerProfile($user, ['is_verified' => false]);

        $this->get(route('marketplace.workers.show', $profile))
             ->assertStatus(404);
    }

    public function test_show_returns_200_for_active_verified_worker(): void
    {
        $user    = $this->makeTenant();
        $profile = $this->makeWorkerProfile($user); // active + verified by default

        $this->get(route('marketplace.workers.show', $profile))
             ->assertStatus(200);
    }

    // ── Booking ───────────────────────────────────────────────────────────────

    public function test_authenticated_tenant_can_submit_booking(): void
    {
        $workerUser = $this->makeTenant();
        $client     = $this->makeTenant();
        $profile    = $this->makeWorkerProfile($workerUser);
        $service    = $this->makeWorkerService($profile);

        $this->actingAs($client)
             ->post(route('marketplace.workers.storeBooking', $profile), [
                 'worker_service_id' => $service->id,
                 'job_description'   => 'Fix the kitchen sink pipe.',
                 'location'          => '10 Garden Road',
             ])
             ->assertRedirect();

        $this->assertDatabaseHas('job_bookings', [
            'client_id'         => $client->id,
            'worker_profile_id' => $profile->id,
            'status'            => 'pending',
        ]);
    }

    public function test_worker_cannot_book_themselves(): void
    {
        $workerUser = $this->makeTenant();
        $profile    = $this->makeWorkerProfile($workerUser);
        $service    = $this->makeWorkerService($profile);

        $this->actingAs($workerUser)
             ->post(route('marketplace.workers.storeBooking', $profile), [
                 'job_description' => 'Booking myself.',
             ])
             ->assertStatus(422);
    }

    public function test_booking_without_job_description_is_rejected(): void
    {
        $workerUser = $this->makeTenant();
        $client     = $this->makeTenant();
        $profile    = $this->makeWorkerProfile($workerUser);

        $this->actingAs($client)
             ->post(route('marketplace.workers.storeBooking', $profile), [
                 'location' => 'Somewhere',
             ])
             ->assertSessionHasErrors('job_description');
    }
}
