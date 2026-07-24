<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Http\Requests\Worker\StoreWorkerReviewRequest;
use App\Models\JobBooking;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $profile = $request->user()->workerProfile ?? abort(404);

        return Inertia::render('Worker/Bookings/Index', [
            'bookings' => $profile->bookings()
                ->with(['client', 'workerService'])
                ->latest()
                ->paginate(20),
        ]);
    }

    public function show(Request $request, JobBooking $booking)
    {
        $profile = $request->user()->workerProfile;
        abort_if(!$profile || $booking->worker_profile_id !== $profile->id, 403);

        return Inertia::render('Worker/Bookings/Show', [
            'booking' => $booking->load(['client', 'workerService', 'review']),
        ]);
    }

    public function accept(Request $request, JobBooking $booking)
    {
        $this->authorizeWorker($request, $booking);
        abort_if($booking->status !== 'pending', 422, 'Only pending bookings can be accepted.');

        $booking->update(['status' => 'accepted', 'accepted_at' => now()]);

        return back()->with('success', 'Booking accepted.');
    }

    public function reject(Request $request, JobBooking $booking)
    {
        $this->authorizeWorker($request, $booking);
        abort_if($booking->status !== 'pending', 422, 'Only pending bookings can be rejected.');

        $request->validate(['rejection_reason' => ['required', 'string', 'max:500']]);

        $booking->update(['status' => 'rejected', 'rejection_reason' => $request->rejection_reason]);

        return back()->with('success', 'Booking rejected.');
    }

    public function markInProgress(Request $request, JobBooking $booking)
    {
        $this->authorizeWorker($request, $booking);
        abort_if($booking->status !== 'accepted', 422, 'Only accepted bookings can be started.');

        $booking->update(['status' => 'in_progress']);

        return back()->with('success', 'Job marked as in progress.');
    }

    public function complete(Request $request, JobBooking $booking)
    {
        $this->authorizeWorker($request, $booking);
        abort_if($booking->status !== 'in_progress', 422, 'Job must be in progress to complete.');

        $request->validate(['worker_notes' => ['nullable', 'string', 'max:500']]);

        $booking->update([
            'status'       => 'completed',
            'completed_at' => now(),
            'worker_notes' => $request->worker_notes,
        ]);

        return back()->with('success', 'Job marked as completed. The client can now leave a review.');
    }

    private function authorizeWorker(Request $request, JobBooking $booking): void
    {
        $profile = $request->user()->workerProfile;
        abort_if(!$profile || $booking->worker_profile_id !== $profile->id, 403);
    }
}
