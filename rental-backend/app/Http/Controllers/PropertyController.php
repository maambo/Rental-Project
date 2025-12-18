<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PropertyController extends Controller
{
    /**
     * Display the specified property.
     */
    public function show($id)
    {
        $property = Property::with(['images', 'landlord'])
            ->withCount('reviews')
            ->findOrFail($id);

        // Increment view count
        $property->increment('view_count');

        // Calculate average rating manually or use an accessor if available
        $property->average_rating = $property->reviews()->avg('rating') ?? 0;

        return Inertia::render('Properties/Show', [
            'property' => $property,
        ]);
    }
}
