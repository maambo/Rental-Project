<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;

class PropertyAvailabilityController extends Controller
{
    public function update(Request $request, $id)
    {
        $request->validate([
            'availability_status' => 'required|in:available,rented,sold',
        ]);

        $property = Property::forLandlord(auth()->id())->findOrFail($id);

        $property->update([
            'availability_status'    => $request->availability_status,
            'availability_changed_at' => now(),
        ]);

        return back()->with('success', 'Availability updated to ' . ucfirst($request->availability_status) . '.');
    }
}
