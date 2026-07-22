<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;

class PropertyReviewController extends Controller
{
    public function store(Request $request, Property $property)
    {
        $validated = $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ]);

        // Allow updating an existing review (same user, same property)
        $property->reviews()->updateOrCreate(
            ['user_id' => auth()->id()],
            ['rating' => $validated['rating'], 'comment' => $validated['comment']]
        );

        return back()->with('success', 'Review submitted successfully!');
    }
}
