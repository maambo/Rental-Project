<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\TourRequest;
use App\Notifications\TourRequestTenantResponded;
use Illuminate\Http\Request;

class TourRequestController extends Controller
{
    public function index(Request $request)
    {
        $tours = TourRequest::where('user_id', auth()->id())
            ->with(['property:id,title,street_address,landlord_id'])
            ->latest()
            ->paginate(15);

        return inertia('Tenant/TourRequests/Index', ['tours' => $tours]);
    }

    public function store(Request $request, Property $property)
    {
        $existing = TourRequest::where('user_id', auth()->id())
            ->where('property_id', $property->id)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($existing) {
            return back()->withErrors([
                'scheduled_at' => 'You already have an active tour request for this property.',
            ]);
        }

        $validated = $request->validate([
            'scheduled_at' => 'required|date|after:now',
            'notes'        => 'nullable|string|max:500',
        ]);

        $scheduledAt = \Carbon\Carbon::parse($validated['scheduled_at']);

        TourRequest::create([
            'property_id'    => $property->id,
            'user_id'        => auth()->id(),
            'name'           => auth()->user()->name,
            'email'          => auth()->user()->email,
            'scheduled_at'   => $scheduledAt,
            'preferred_date' => $scheduledAt->toDateString(),
            'preferred_time' => $scheduledAt->format('H:i'),
            'notes'          => $validated['notes'] ?? null,
            'status'         => 'pending',
        ]);

        return back()->with('success', 'Tour request scheduled successfully!');
    }

    public function accept(TourRequest $tourRequest)
    {
        abort_if($tourRequest->user_id !== auth()->id(), 403);
        abort_if($tourRequest->status !== 'rescheduled', 422, 'This tour request cannot be accepted in its current state.');

        $tourRequest->update(['status' => 'approved']);

        $this->notifyLandlord($tourRequest);

        return back()->with('success', 'You have accepted the rescheduled tour.');
    }

    public function decline(TourRequest $tourRequest)
    {
        abort_if($tourRequest->user_id !== auth()->id(), 403);
        abort_if($tourRequest->status !== 'rescheduled', 422, 'This tour request cannot be declined in its current state.');

        $tourRequest->update(['status' => 'pending']);

        $this->notifyLandlord($tourRequest);

        return back()->with('success', 'You have declined the reschedule. The request is back to pending.');
    }

    private function notifyLandlord(TourRequest $tourRequest): void
    {
        $tourRequest->load('property.landlord');
        $tourRequest->property?->landlord?->notify(new TourRequestTenantResponded($tourRequest));
    }
}
