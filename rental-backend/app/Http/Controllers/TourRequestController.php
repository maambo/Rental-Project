<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TourRequestController extends Controller
{
    public function store(Request $request, Property $property)
    {
        $validated = $request->validate([
            'scheduled_at' => 'required|date|after:now',
            'notes' => 'nullable|string',
        ]);

        DB::table('tour_requests')->insert([
            'user_id' => auth()->id(),
            'property_id' => $property->id,
            'status' => 'pending',
            'scheduled_at' => $validated['scheduled_at'],
            'notes' => $validated['notes'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Tour request scheduled successfully!');
    }
}
