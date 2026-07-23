<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\TourRequest;
use Illuminate\Http\Request;

class TourRequestController extends Controller
{
    public function store(Request $request, Property $property)
    {
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
}
