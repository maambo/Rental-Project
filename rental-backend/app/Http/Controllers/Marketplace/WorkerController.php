<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Http\Requests\Worker\StoreJobBookingRequest;
use App\Models\JobBooking;
use App\Models\TradeCategory;
use App\Models\Town;
use App\Models\WorkerProfile;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WorkerController extends Controller
{
    public function index(Request $request)
    {
        $query = WorkerProfile::with(['user', 'category', 'town'])
            ->active()
            ->verified();

        if ($request->filled('category')) {
            $query->inCategory((int) $request->category);
        }

        if ($request->filled('town')) {
            $query->where('town_id', $request->town);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $workers = $query->orderByDesc('is_featured')
            ->orderByDesc('rating_average')
            ->paginate(16)
            ->withQueryString();

        return Inertia::render('Marketplace/Workers/Index', [
            'workers'    => $workers,
            'categories' => TradeCategory::active()->get(['id', 'name', 'icon']),
            'towns'      => Town::orderBy('name')->get(['id', 'name']),
            'filters'    => $request->only(['category', 'town', 'search']),
        ]);
    }

    public function show(WorkerProfile $workerProfile)
    {
        abort_if(!$workerProfile->is_active || !$workerProfile->is_verified, 404);

        return Inertia::render('Marketplace/Workers/Show', [
            'worker'   => $workerProfile->load(['user', 'category', 'town', 'services', 'portfolioPhotos', 'reviews.client']),
            'services' => $workerProfile->services()->get(),
        ]);
    }

    public function book(Request $request, WorkerProfile $workerProfile)
    {
        abort_if(!$workerProfile->is_active, 404);

        return Inertia::render('Marketplace/Workers/Book', [
            'worker'   => $workerProfile->load(['user', 'category']),
            'services' => $workerProfile->services()->get(),
        ]);
    }

    public function storeBooking(StoreJobBookingRequest $request, WorkerProfile $workerProfile)
    {
        abort_if(!$workerProfile->is_active, 404);
        abort_if($workerProfile->user_id === $request->user()->id, 422, 'You cannot book yourself.');

        $booking = JobBooking::create(array_merge($request->validated(), [
            'client_id'         => $request->user()->id,
            'worker_profile_id' => $workerProfile->id,
            'status'            => 'pending',
        ]));

        return redirect()->route('marketplace.bookings.show', $booking)->with('success', 'Booking request sent to the worker.');
    }
}
