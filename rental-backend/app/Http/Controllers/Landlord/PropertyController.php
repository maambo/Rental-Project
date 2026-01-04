<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class PropertyController extends Controller
{
    /**
     * Display a listing of landlord's properties.
     */
    public function index()
    {
        $properties = Property::with('images')
            ->where('landlord_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        $landlordApplication = DB::table('landlord_applications')
            ->where('user_id', auth()->id())
            ->where('status', 'approved')
            ->first();

        $tier = $landlordApplication ? $landlordApplication->tier : 'small';

        return Inertia::render('Landlord/Properties/Index', [
            'properties' => $properties,
            'tier' => $tier,
        ]);
    }

    /**
     * Show the form for creating a new property.
     */
    public function create()
    {
        // Check tier limits
        $landlordApplication = DB::table('landlord_applications')
            ->where('user_id', auth()->id())
            ->where('status', 'approved')
            ->first();

        if (!$landlordApplication) {
            return redirect()->route('dashboard')
                ->with('error', 'You must be an approved landlord to add properties.');
        }

        $tier = $landlordApplication->tier;
        $tierLimits = [
            'small' => 10,
            'medium' => 50,
            'large' => null,
        ];

        $currentPropertyCount = Property::where('landlord_id', auth()->id())->count();
        $limit = $tierLimits[$tier];

        if ($limit && $currentPropertyCount >= $limit) {
            return redirect()->route('landlord.properties.index')
                ->with('error', "You have reached your tier limit of {$limit} properties. Upgrade your tier to add more.");
        }

        return Inertia::render('Landlord/Properties/Create', [
            'tier' => $tier,
            'currentCount' => $currentPropertyCount,
            'limit' => $limit,
        ]);
    }

    /**
     * Store a newly created property in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'location' => 'required|string|max:255',
            'province' => 'required|string|max:100',
            'town' => 'required|string|max:100',
            'bedrooms' => 'required|integer|min:0',
            'bathrooms' => 'required|integer|min:0',
            'square_feet' => 'required|integer|min:0',
            'amenities' => 'nullable|array',
            'images' => 'required|array|min:1|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:5120',
        ]);

        // Create property
        $property = Property::create([
            'landlord_id' => auth()->id(),
            'title' => $validated['title'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'location' => $validated['location'],
            'province' => $validated['province'],
            'town' => $validated['town'],
            'bedrooms' => $validated['bedrooms'],
            'bathrooms' => $validated['bathrooms'],
            'square_feet' => $validated['square_feet'],
            'amenities' => $validated['amenities'] ?? [],
            'approval_status' => 'pending',
        ]);

        // Upload images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('properties', 'public');
                
                DB::table('property_images')->insert([
                    'property_id' => $property->id,
                    'image_url' => $path,
                    'is_primary' => $index === 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return redirect()->route('landlord.properties.index')
            ->with('success', 'Property created successfully and submitted for approval.');
    }

    /**
     * Show the form for editing the specified property.
     */
    public function edit($id)
    {
        $property = Property::with('images')
            ->where('id', $id)
            ->where('landlord_id', auth()->id())
            ->firstOrFail();

        // Only allow editing if pending or rejected
        if (!in_array($property->approval_status, ['pending', 'rejected'])) {
            return redirect()->route('landlord.properties.index')
                ->with('error', 'You can only edit properties that are pending or rejected.');
        }

        return Inertia::render('Landlord/Properties/Edit', [
            'property' => $property,
        ]);
    }

    /**
     * Update the specified property in storage.
     */
    public function update(Request $request, $id)
    {
        $property = Property::where('id', $id)
            ->where('landlord_id', auth()->id())
            ->firstOrFail();

        // Only allow editing if pending or rejected
        if (!in_array($property->approval_status, ['pending', 'rejected'])) {
            return back()->with('error', 'You can only edit properties that are pending or rejected.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'location' => 'required|string|max:255',
            'province' => 'required|string|max:100',
            'town' => 'required|string|max:100',
            'bedrooms' => 'required|integer|min:0',
            'bathrooms' => 'required|integer|min:0',
            'square_feet' => 'required|integer|min:0',
            'amenities' => 'nullable|array',
            'new_images' => 'nullable|array|max:10',
            'new_images.*' => 'image|mimes:jpeg,png,jpg|max:5120',
            'remove_images' => 'nullable|array',
        ]);

        // Update property
        $property->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'location' => $validated['location'],
            'province' => $validated['province'],
            'town' => $validated['town'],
            'bedrooms' => $validated['bedrooms'],
            'bathrooms' => $validated['bathrooms'],
            'square_feet' => $validated['square_feet'],
            'amenities' => $validated['amenities'] ?? [],
            'approval_status' => 'pending', // Reset to pending after edit
        ]);

        // Remove selected images
        if (!empty($validated['remove_images'])) {
            $imagesToRemove = DB::table('property_images')
                ->whereIn('id', $validated['remove_images'])
                ->where('property_id', $property->id)
                ->get();

            foreach ($imagesToRemove as $image) {
                Storage::disk('public')->delete($image->image_url);
            }

            DB::table('property_images')
                ->whereIn('id', $validated['remove_images'])
                ->delete();
        }

        // Add new images
        if ($request->hasFile('new_images')) {
            $existingImagesCount = DB::table('property_images')
                ->where('property_id', $property->id)
                ->count();

            foreach ($request->file('new_images') as $image) {
                $path = $image->store('properties', 'public');
                
                DB::table('property_images')->insert([
                    'property_id' => $property->id,
                    'image_url' => $path,
                    'is_primary' => $existingImagesCount === 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $existingImagesCount++;
            }
        }

        return redirect()->route('landlord.properties.index')
            ->with('success', 'Property updated successfully and resubmitted for approval.');
    }

    /**
     * Remove the specified property from storage.
     */
    public function destroy($id)
    {
        $property = Property::where('id', $id)
            ->where('landlord_id', auth()->id())
            ->firstOrFail();

        // Delete images
        $images = DB::table('property_images')
            ->where('property_id', $property->id)
            ->get();

        foreach ($images as $image) {
            Storage::disk('public')->delete($image->image_url);
        }

        DB::table('property_images')->where('property_id', $property->id)->delete();

        // Delete property
        $property->delete();

        return redirect()->route('landlord.properties.index')
            ->with('success', 'Property deleted successfully.');
    }
}
