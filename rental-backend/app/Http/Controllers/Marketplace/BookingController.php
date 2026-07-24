<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Http\Requests\Worker\StoreWorkerReviewRequest;
use App\Models\JobBooking;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $bookings = JobBooking::with(['workerProfile.user', 'workerProfile.category', 'workerService'])
            ->where('client_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return Inertia::render('Marketplace/Bookings/Index', [
            'bookings' => $bookings,
        ]);
    }

    public function show(Request $request, JobBooking $booking)
    {
        abort_if($booking->client_id !== $request->user()->id, 403);

        return Inertia::render('Marketplace/Bookings/Show', [
            'booking' => $booking->load(['workerProfile.user', 'workerService', 'review']),
        ]);
    }

    public function cancel(Request $request, JobBooking $booking)
    {
        abort_if($booking->client_id !== $request->user()->id, 403);
        abort_if(!in_array($booking->status, ['pending', 'accepted']), 422, 'This booking cannot be cancelled.');

        $booking->update(['status' => 'cancelled']);

        return back()->with('success', 'Booking cancelled.');
    }

    public function review(StoreWorkerReviewRequest $request, JobBooking $booking)
    {
        abort_if($booking->client_id !== $request->user()->id, 403);
        abort_if(!$booking->isReviewable(), 422, 'This booking cannot be reviewed.');

        $booking->review()->create(array_merge($request->validated(), [
            'client_id'         => $request->user()->id,
            'worker_profile_id' => $booking->worker_profile_id,
        ]));

        return back()->with('success', 'Review submitted. Thank you!');
    }
}
