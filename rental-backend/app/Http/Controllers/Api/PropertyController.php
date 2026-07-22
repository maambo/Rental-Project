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
    public function index(Request $request)
    {
        $properties = Property::search($request->all())
            ->with(['images', 'landlord', 'province', 'district', 'town'])
            ->where('approval_status', 'approved')
            ->where('is_visible_in_search', true)
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json($properties);
    }

    /**
     * Get properties for map display, grouped by type.
     */
    public function map(Request $request)
    {
        $properties = Property::search($request->all())
            ->where('approval_status', 'approved')
            ->where('is_visible_in_search', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->select(['id', 'title', 'price', 'latitude', 'longitude', 'property_type', 'listing_type', 'property_subtype'])
            ->get();

        return response()->json($properties);
    }

    /**
     * Display the specified property.
     */
    public function show($id)
    {
        $property = Property::with([
            'images', 'reviews.user', 'landlord',
            'province', 'district', 'town',
            'utilities.options',
        ])->findOrFail($id);

        $property->increment('view_count');

        $property->average_rating = $property->averageRating;
        $property->review_count   = $property->reviewCount;

        return response()->json($property);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'province_id'      => 'required|exists:provinces,id',
            'district_id'      => 'required|exists:districts,id',
            'town_id'          => 'required|exists:towns,id',
            'street_address'   => 'required|string|max:255',
            'latitude'         => 'required|numeric|between:-18.1,-8.2',
            'longitude'        => 'required|numeric|between:21.9,33.7',
            'property_type'    => 'required|in:residential,commercial',
            'property_subtype' => 'required|in:house,apartment,room,farm,plot,shop,office_space,warehouse',
            'listing_type'     => 'required|in:rent,sale',
            'title'            => 'required|string|max:255',
            'description'      => 'required|string',
            'terms_and_conditions' => 'nullable|string',
            'price'            => 'required|numeric|min:0',
            'bedrooms'         => 'nullable|integer|min:0',
            'bathrooms'        => 'nullable|integer|min:0',
            'square_feet'      => 'nullable|integer|min:0',
            'amenities'        => 'nullable|array',
            'amenities.*'      => 'string',
        ]);

        $property = Property::create(array_merge($validated, [
            'landlord_id'     => $request->user()->id,
            'approval_status' => 'pending',
            'submitted_date'  => now(),
        ]));

        return response()->json($property->load(['province', 'district', 'town']), 201);
    }

    public function update(Request $request, $id)
    {
        $property = Property::findOrFail($id);

        if ($property->landlord_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'province_id'      => 'sometimes|exists:provinces,id',
            'district_id'      => 'sometimes|exists:districts,id',
            'town_id'          => 'sometimes|exists:towns,id',
            'street_address'   => 'sometimes|string|max:255',
            'latitude'         => 'sometimes|numeric|between:-18.1,-8.2',
            'longitude'        => 'sometimes|numeric|between:21.9,33.7',
            'property_type'    => 'sometimes|in:residential,commercial',
            'property_subtype' => 'sometimes|in:house,apartment,room,farm,plot,shop,office_space,warehouse',
            'listing_type'     => 'sometimes|in:rent,sale',
            'title'            => 'sometimes|string|max:255',
            'description'      => 'sometimes|string',
            'terms_and_conditions' => 'nullable|string',
            'price'            => 'sometimes|numeric|min:0',
            'bedrooms'         => 'nullable|integer|min:0',
            'bathrooms'        => 'nullable|integer|min:0',
            'square_feet'      => 'nullable|integer|min:0',
            'amenities'        => 'nullable|array',
            'amenities.*'      => 'string',
        ]);

        $property->update($validated);

        return response()->json($property->load(['province', 'district', 'town']));
    }

    public function destroy(Request $request, $id)
    {
        $property = Property::findOrFail($id);

        if ($property->landlord_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $property->delete();

        return response()->json(['message' => 'Property deleted successfully']);
    }
}
