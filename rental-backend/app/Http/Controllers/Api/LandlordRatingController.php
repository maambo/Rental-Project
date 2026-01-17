<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LandlordRating;
use App\Models\ViewingConfirmation;
use Illuminate\Http\Request;

class LandlordRatingController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'landlord_id' => 'required|exists:users,id',
            'property_id' => 'nullable|exists:properties,id',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
        ]);

        // Check verification (did they confirm viewing?)
        $isVerified = false;
        if ($validated['property_id']) {
            $isVerified = ViewingConfirmation::where('property_id', $validated['property_id'])
                ->where('tenant_id', auth()->id())
                ->exists();
        }

        $rating = LandlordRating::updateOrCreate(
            [
                'landlord_id' => $validated['landlord_id'],
                'tenant_id' => auth()->id(),
                'property_id' => $validated['property_id'],
            ],
            [
                'rating' => $validated['rating'],
                'review' => $validated['review'] ?? null,
                'is_verified_rental' => $isVerified,
            ]
        );

        return response()->json(['message' => 'Rating submitted', 'rating' => $rating], 201);
    }
}
