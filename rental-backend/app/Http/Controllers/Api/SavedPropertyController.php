<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SavedPropertyController extends Controller
{
    /**
     * Display a listing of the user's saved properties.
     */
    public function index(Request $request)
    {
        $savedProperties = $request->user()
            ->savedProperties()
            ->with(['property.images', 'property.landlord'])
            ->latest()
            ->paginate(20);

        return response()->json($savedProperties);
    }

    /**
     * Store a newly created saved property.
     */
    public function store(Request $request)
    {
        $request->validate([
            'property_id' => 'required|exists:properties,id',
        ]);

        $savedProperty = $request->user()->savedProperties()->firstOrCreate([
            'property_id' => $request->property_id,
        ]);

        return response()->json($savedProperty, 201);
    }

    /**
     * Remove the specified saved property.
     */
    public function destroy(Request $request, $property_id)
    {
        $request->user()->savedProperties()
            ->where('property_id', $property_id)
            ->delete();

        return response()->json(['message' => 'Property removed from wishlist']);
    }
}
