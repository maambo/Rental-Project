<?php

namespace App\Http\Controllers;

use App\Http\Requests\PropertyApplication\StorePropertyApplicationRequest;
use App\Models\Property;
use App\Models\PropertyApplication;
use Inertia\Inertia;

class PropertyApplicationController extends Controller
{
    public function create(Property $property)
    {
        // Block applications on for-sale properties — handled by a different flow
        if ($property->isForSale()) {
            return back()->with('error', 'This property is listed for sale, not for rent.');
        }

        return Inertia::render('Properties/Apply', [
            'property' => $property->load(['province', 'district', 'town', 'images']),
        ]);
    }

    public function store(StorePropertyApplicationRequest $request, Property $property)
    {
        if ($property->isForSale()) {
            return back()->with('error', 'This property is listed for sale, not for rent.');
        }

        // Prevent duplicate pending applications
        $existing = PropertyApplication::where('user_id', auth()->id())
            ->where('property_id', $property->id)
            ->where('status', 'pending')
            ->exists();

        if ($existing) {
            return back()->with('error', 'You already have a pending application for this property.');
        }

        $data = array_merge(
            $request->only(['message', 'preferred_move_in', 'additional_comments']),
            [
                'user_id'     => auth()->id(),
                'property_id' => $property->id,
                'status'      => 'pending',
            ]
        );

        if ($property->isResidentialRent()) {
            $data = array_merge($data, $request->only(['adults', 'children', 'has_pets', 'pet_details']));
        }

        if ($property->isCommercial()) {
            $data = array_merge($data, $request->only(['intended_use', 'business_name']));
        }

        PropertyApplication::create($data);

        return redirect()->route('dashboard')
            ->with('success', 'Application submitted. The landlord will be in touch.');
    }
}
