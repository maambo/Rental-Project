<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Models\TourRequest;
use App\Notifications\TourRequestStatusUpdated;
use Illuminate\Http\Request;

class TourRequestController extends Controller
{
    public function index()
    {
        $tours = TourRequest::whereHas('property', function ($q) {
            $q->where('landlord_id', auth()->id());
        })
        ->with(['property:id,title', 'user:id,name,email'])
        ->latest()
        ->paginate(20);

        $counts = [
            'total'    => $tours->total(),
            'pending'  => TourRequest::whereHas('property', fn($q) => $q->where('landlord_id', auth()->id()))->where('status', 'pending')->count(),
            'approved' => TourRequest::whereHas('property', fn($q) => $q->where('landlord_id', auth()->id()))->where('status', 'approved')->count(),
            'rejected' => TourRequest::whereHas('property', fn($q) => $q->where('landlord_id', auth()->id()))->where('status', 'rejected')->count(),
        ];

        return inertia('Landlord/TourRequests/Index', [
            'tours'  => $tours,
            'counts' => $counts,
        ]);
    }

    public function approve(Request $request, TourRequest $tourRequest)
    {
        $this->authorise($tourRequest);

        $validated = $request->validate([
            'landlord_response' => 'nullable|string|max:500',
        ]);

        $tourRequest->update([
            'status'            => 'approved',
            'landlord_response' => $validated['landlord_response'] ?? null,
        ]);

        $tourRequest->load('property');
        $tourRequest->user?->notify(new TourRequestStatusUpdated($tourRequest));

        return back()->with('success', 'Tour request approved.');
    }

    public function reject(Request $request, TourRequest $tourRequest)
    {
        $this->authorise($tourRequest);

        $validated = $request->validate([
            'landlord_response' => 'nullable|string|max:500',
        ]);

        $tourRequest->update([
            'status'            => 'rejected',
            'landlord_response' => $validated['landlord_response'] ?? null,
        ]);

        $tourRequest->load('property');
        $tourRequest->user?->notify(new TourRequestStatusUpdated($tourRequest));

        return back()->with('success', 'Tour request declined.');
    }

    public function update(Request $request, TourRequest $tourRequest)
    {
        $this->authorise($tourRequest);

        $validated = $request->validate([
            'scheduled_at'      => 'required|date|after:now',
            'landlord_response' => 'nullable|string|max:500',
        ]);

        $tourRequest->update([
            'scheduled_at'      => $validated['scheduled_at'],
            'landlord_response' => $validated['landlord_response'] ?? null,
            'status'            => 'rescheduled',
        ]);

        $tourRequest->load('property');
        $tourRequest->user?->notify(new TourRequestStatusUpdated($tourRequest));

        return back()->with('success', 'Tour rescheduled. Tenant has been notified to accept or decline.');
    }

    private function authorise(TourRequest $tourRequest): void
    {
        abort_if(
            $tourRequest->property?->landlord_id !== auth()->id(),
            403,
            'This tour request does not belong to your property.',
        );
    }
}
