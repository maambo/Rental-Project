<?php

namespace Tests\Feature\Worker;

use App\Models\WorkerReview;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RentalTestSupport;

class WorkerBookingWorkflowTest extends TestCase
{
    use RefreshDatabase, RentalTestSupport;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
    }

    // ── Worker-side state machine ─────────────────────────────────────────────

    public function test_worker_can_accept_pending_booking(): void
    {
        $workerUser = $this->makeTenant();
        $client     = $this->makeTenant();
        $profile    = $this->makeWorkerProfile($workerUser);
        $service    = $this->makeWorkerService($profile);
        $booking    = $this->makeJobBooking($client, $profile, $service, ['status' => 'pending']);

        $this->actingAs($workerUser)
             ->post(route('worker.bookings.accept', $booking))
             ->assertRedirect();

        $booking->refresh();
        $this->assertEquals('accepted', $booking->status);
        $this->assertNotNull($booking->accepted_at);
    }

    public function test_worker_can_reject_pending_booking_with_reason(): void
    {
        $workerUser = $this->makeTenant();
        $client     = $this->makeTenant();
        $profile    = $this->makeWorkerProfile($workerUser);
        $service    = $this->makeWorkerService($profile);
        $booking    = $this->makeJobBooking($client, $profile, $service, ['status' => 'pending']);

        $this->actingAs($workerUser)
             ->post(route('worker.bookings.reject', $booking), ['rejection_reason' => 'I am unavailable.'])
             ->assertRedirect();

        $booking->refresh();
        $this->assertEquals('rejected', $booking->status);
        $this->assertEquals('I am unavailable.', $booking->rejection_reason);
    }

    public function test_worker_can_mark_accepted_booking_in_progress(): void
    {
        $workerUser = $this->makeTenant();
        $client     = $this->makeTenant();
        $profile    = $this->makeWorkerProfile($workerUser);
        $service    = $this->makeWorkerService($profile);
        $booking    = $this->makeJobBooking($client, $profile, $service, ['status' => 'accepted']);

        $this->actingAs($workerUser)
             ->post(route('worker.bookings.in-progress', $booking))
             ->assertRedirect();

        $booking->refresh();
        $this->assertEquals('in_progress', $booking->status);
    }

    public function test_worker_can_complete_in_progress_booking(): void
    {
        $workerUser = $this->makeTenant();
        $client     = $this->makeTenant();
        $profile    = $this->makeWorkerProfile($workerUser);
        $service    = $this->makeWorkerService($profile);
        $booking    = $this->makeJobBooking($client, $profile, $service, ['status' => 'in_progress']);

        $this->actingAs($workerUser)
             ->post(route('worker.bookings.complete', $booking), ['worker_notes' => 'Job done.'])
             ->assertRedirect();

        $booking->refresh();
        $this->assertEquals('completed', $booking->status);
        $this->assertNotNull($booking->completed_at);
    }

    public function test_worker_cannot_accept_booking_belonging_to_another_worker(): void
    {
        $workerA = $this->makeTenant();
        $workerB = $this->makeTenant();
        $client  = $this->makeTenant();

        $profileA = $this->makeWorkerProfile($workerA);
        $serviceA = $this->makeWorkerService($profileA);
        $booking  = $this->makeJobBooking($client, $profileA, $serviceA, ['status' => 'pending']);

        // workerB has no profile that owns this booking.
        $this->actingAs($workerB)
             ->post(route('worker.bookings.accept', $booking))
             ->assertStatus(403);
    }

    public function test_worker_cannot_complete_a_pending_booking(): void
    {
        $workerUser = $this->makeTenant();
        $client     = $this->makeTenant();
        $profile    = $this->makeWorkerProfile($workerUser);
        $service    = $this->makeWorkerService($profile);
        $booking    = $this->makeJobBooking($client, $profile, $service, ['status' => 'pending']);

        $this->actingAs($workerUser)
             ->post(route('worker.bookings.complete', $booking))
             ->assertStatus(422);
    }

    // ── Client-side state machine (Marketplace/BookingController) ─────────────

    public function test_client_can_cancel_pending_booking(): void
    {
        $workerUser = $this->makeTenant();
        $client     = $this->makeTenant();
        $profile    = $this->makeWorkerProfile($workerUser);
        $service    = $this->makeWorkerService($profile);
        $booking    = $this->makeJobBooking($client, $profile, $service, ['status' => 'pending']);

        $this->actingAs($client)
             ->post(route('marketplace.bookings.cancel', $booking))
             ->assertRedirect();

        $booking->refresh();
        $this->assertEquals('cancelled', $booking->status);
    }

    public function test_client_can_cancel_accepted_booking(): void
    {
        $workerUser = $this->makeTenant();
        $client     = $this->makeTenant();
        $profile    = $this->makeWorkerProfile($workerUser);
        $service    = $this->makeWorkerService($profile);
        $booking    = $this->makeJobBooking($client, $profile, $service, ['status' => 'accepted']);

        $this->actingAs($client)
             ->post(route('marketplace.bookings.cancel', $booking))
             ->assertRedirect();

        $booking->refresh();
        $this->assertEquals('cancelled', $booking->status);
    }

    public function test_client_cannot_cancel_completed_booking(): void
    {
        $workerUser = $this->makeTenant();
        $client     = $this->makeTenant();
        $profile    = $this->makeWorkerProfile($workerUser);
        $service    = $this->makeWorkerService($profile);
        $booking    = $this->makeJobBooking($client, $profile, $service, ['status' => 'completed']);

        $this->actingAs($client)
             ->post(route('marketplace.bookings.cancel', $booking))
             ->assertStatus(422);
    }

    public function test_client_can_leave_review_on_completed_booking(): void
    {
        $workerUser = $this->makeTenant();
        $client     = $this->makeTenant();
        $profile    = $this->makeWorkerProfile($workerUser);
        $service    = $this->makeWorkerService($profile);
        $booking    = $this->makeJobBooking($client, $profile, $service, ['status' => 'completed']);

        $this->actingAs($client)
             ->post(route('marketplace.bookings.review', $booking), [
                 'rating'  => 5,
                 'comment' => 'Excellent work!',
             ])
             ->assertRedirect();

        $this->assertDatabaseHas('worker_reviews', [
            'job_booking_id'   => $booking->id,
            'client_id'        => $client->id,
            'rating'           => 5,
        ]);
    }

    public function test_client_cannot_review_non_completed_booking(): void
    {
        $workerUser = $this->makeTenant();
        $client     = $this->makeTenant();
        $profile    = $this->makeWorkerProfile($workerUser);
        $service    = $this->makeWorkerService($profile);
        $booking    = $this->makeJobBooking($client, $profile, $service, ['status' => 'in_progress']);

        $this->actingAs($client)
             ->post(route('marketplace.bookings.review', $booking), ['rating' => 4])
             ->assertStatus(422);
    }

    public function test_client_cannot_review_same_booking_twice(): void
    {
        $workerUser = $this->makeTenant();
        $client     = $this->makeTenant();
        $profile    = $this->makeWorkerProfile($workerUser);
        $service    = $this->makeWorkerService($profile);
        $booking    = $this->makeJobBooking($client, $profile, $service, ['status' => 'completed']);

        WorkerReview::create([
            'job_booking_id'   => $booking->id,
            'client_id'        => $client->id,
            'worker_profile_id' => $profile->id,
            'rating'           => 4,
        ]);

        $this->actingAs($client)
             ->post(route('marketplace.bookings.review', $booking), ['rating' => 3])
             ->assertStatus(422);
    }

    // ── Fee / Model logic ─────────────────────────────────────────────────────

    public function test_calculate_fees_with_8_percent_commission(): void
    {
        $workerUser = $this->makeTenant();
        $client     = $this->makeTenant();
        $profile    = $this->makeWorkerProfile($workerUser);
        $service    = $this->makeWorkerService($profile);
        $booking    = $this->makeJobBooking($client, $profile, $service);

        $booking->calculateFees(1000.00, 0.08);

        $booking->refresh();
        $this->assertEquals(80.00, (float) $booking->platform_fee);
        $this->assertEquals(920.00, (float) $booking->worker_net);
        $this->assertEquals(1000.00, (float) $booking->agreed_price);
    }

    public function test_calculate_fees_with_10_percent_commission(): void
    {
        $workerUser = $this->makeTenant();
        $client     = $this->makeTenant();
        $profile    = $this->makeWorkerProfile($workerUser);
        $service    = $this->makeWorkerService($profile);
        $booking    = $this->makeJobBooking($client, $profile, $service);

        $booking->calculateFees(1000.00, 0.10);

        $booking->refresh();
        $this->assertEquals(100.00, (float) $booking->platform_fee);
        $this->assertEquals(900.00, (float) $booking->worker_net);
    }

    public function test_is_reviewable_true_when_completed_with_no_review(): void
    {
        $workerUser = $this->makeTenant();
        $client     = $this->makeTenant();
        $profile    = $this->makeWorkerProfile($workerUser);
        $service    = $this->makeWorkerService($profile);
        $booking    = $this->makeJobBooking($client, $profile, $service, ['status' => 'completed']);

        $this->assertTrue($booking->isReviewable());
    }

    public function test_is_reviewable_false_when_review_exists(): void
    {
        $workerUser = $this->makeTenant();
        $client     = $this->makeTenant();
        $profile    = $this->makeWorkerProfile($workerUser);
        $service    = $this->makeWorkerService($profile);
        $booking    = $this->makeJobBooking($client, $profile, $service, ['status' => 'completed']);

        WorkerReview::create([
            'job_booking_id'    => $booking->id,
            'client_id'         => $client->id,
            'worker_profile_id' => $profile->id,
            'rating'            => 5,
        ]);

        $this->assertFalse($booking->isReviewable());
    }

    public function test_is_reviewable_false_when_status_is_pending(): void
    {
        $workerUser = $this->makeTenant();
        $client     = $this->makeTenant();
        $profile    = $this->makeWorkerProfile($workerUser);
        $service    = $this->makeWorkerService($profile);
        $booking    = $this->makeJobBooking($client, $profile, $service, ['status' => 'pending']);

        $this->assertFalse($booking->isReviewable());
    }
}
