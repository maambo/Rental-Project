<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ViewingConfirmation;
use App\Models\Property;
use Illuminate\Http\Request;

class ViewingConfirmationController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
        ]);

        $property = Property::with('landlord')->findOrFail($validated['property_id']);

        // Check if already confirmed
        $exists = ViewingConfirmation::where('property_id', $property->id)
            ->where('tenant_id', auth()->id())
            ->exists();
            
        if ($exists) {
            return response()->json(['message' => 'Viewing already confirmed'], 409);
        }

        ViewingConfirmation::create([
            'property_id' => $property->id,
            'tenant_id' => auth()->id(),
            'landlord_id' => $property->landlord_id,
            'confirmed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Viewing confirmed',
            'landlord_phone' => $property->landlord->phone ?? 'Not available' // Reveal phone
        ], 201);
    }
}
