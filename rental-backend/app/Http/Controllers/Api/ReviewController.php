<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyReview;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Store a new review for a property.
     */
    public function store(Request $request, $propertyId)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ]);

        $property = Property::findOrFail($propertyId);

        // Check if user already reviewed this property
        $existingReview = PropertyReview::where('property_id', $propertyId)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existingReview) {
            // Update existing review
            $existingReview->update($validated);
            return response()->json($existingReview);
        }

        // Create new review
        $review = PropertyReview::create([
            'property_id' => $propertyId,
            'user_id' => $request->user()->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
        ]);

        return response()->json($review->load('user'), 201);
    }
}
