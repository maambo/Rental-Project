<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\TourRequest;
use Illuminate\Http\Request;

class TourRequestController extends Controller
{
    /**
     * Store a new tour request.
     */
    public function store(Request $request, $propertyId)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'preferred_date' => 'required|date|after:today',
            'preferred_time' => 'required|date_format:H:i',
            'notes' => 'nullable|string|max:500',
        ]);

        $property = Property::findOrFail($propertyId);

        $tourRequest = TourRequest::create([
            'property_id' => $propertyId,
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'preferred_date' => $validated['preferred_date'],
            'preferred_time' => $validated['preferred_time'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
        ]);

        return response()->json($tourRequest, 201);
    }

    /**
     * Get tour requests for landlord's properties.
     */
    public function index(Request $request)
    {
        $tourRequests = TourRequest::whereHas('property', function ($query) use ($request) {
            $query->where('landlord_id', $request->user()->id);
        })
        ->with(['property', 'user'])
        ->orderBy('created_at', 'desc')
        ->paginate(20);

        return response()->json($tourRequests);
    }

    /**
     * Approve a tour request.
     */
    public function approve(Request $request, $id)
    {
        $tourRequest = TourRequest::findOrFail($id);

        // Ensure user owns the property
        if ($tourRequest->property->landlord_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'landlord_response' => 'nullable|string|max:500',
        ]);

        $tourRequest->update([
            'status' => 'approved',
            'landlord_response' => $validated['landlord_response'] ?? null,
        ]);

        return response()->json($tourRequest);
    }
}
