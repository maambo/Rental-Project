<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    /**
     * Display a listing of approved properties.
     */
    public function index()
    {
        $properties = Property::with(['images', 'landlord'])
            ->where('is_approved', true)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($properties);
    }

    /**
     * Display the specified property.
     */
    public function show($id)
    {
        $property = Property::with(['images', 'reviews.user', 'landlord'])
            ->findOrFail($id);

        // Increment view count
        $property->increment('view_count');

        // Add computed attributes
        $property->average_rating = $property->averageRating;
        $property->review_count = $property->reviewCount;

        return response()->json($property);
    }

    /**
     * Store a newly created property (landlord only).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'location' => 'required|string',
            'bedrooms' => 'required|integer|min:0',
            'bathrooms' => 'required|integer|min:0',
            'sqft' => 'required|integer|min:0',
            'images' => 'required|array|min:1',
            'images.*' => 'required|url',
        ]);

        // Generate property code
        $lastProperty = Property::latest('id')->first();
        $nextNumber = $lastProperty ? (int)substr($lastProperty->code, 3) + 1 : 1;
        $code = 'PIV' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        $property = Property::create([
            'landlord_id' => $request->user()->id,
            'code' => $code,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'location' => $validated['location'],
            'bedrooms' => $validated['bedrooms'],
            'bathrooms' => $validated['bathrooms'],
            'sqft' => $validated['sqft'],
            'approval_status' => 'pending',
            'submitted_date' => now(),
        ]);

        // Create property images
        foreach ($validated['images'] as $index => $imageUrl) {
            $property->images()->create([
                'image_url' => $imageUrl,
                'order' => $index,
                'is_primary' => $index === 0,
            ]);
        }

        return response()->json($property->load('images'), 201);
    }

    /**
     * Update the specified property.
     */
    public function update(Request $request, $id)
    {
        $property = Property::findOrFail($id);

        // Ensure user owns the property
        if ($property->landlord_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'location' => 'sometimes|string',
            'bedrooms' => 'sometimes|integer|min:0',
            'bathrooms' => 'sometimes|integer|min:0',
            'sqft' => 'sometimes|integer|min:0',
        ]);

        $property->update($validated);

        return response()->json($property);
    }

    /**
     * Remove the specified property.
     */
    public function destroy(Request $request, $id)
    {
        $property = Property::findOrFail($id);

        // Ensure user owns the property
        if ($property->landlord_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $property->delete();

        return response()->json(['message' => 'Property deleted successfully']);
    }
}
