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
        // Check verification and limits
        $application = \App\Models\LandlordApplication::where('user_id', auth()->id())
            ->where('status', 'approved')
            ->first();

        if (!$application) {
            return redirect()->route('dashboard')
                ->with('error', 'You must be an approved landlord to add properties.');
        }

        $currentPropertyCount = Property::where('landlord_id', auth()->id())->count();
        $limit = $application->listing_limit;

        // If limit is 0 or null, we might consider it unlimited (Premium), 
        // but based on migration defaults, it's an integer. 
        // Let's assume -1 or a high number could be unlimited, but for now we rely on the DB value.
        // If the user is Premium, the limit should be set high in the DB or handled here.
        // For now, respect the DB value.
        
        if ($currentPropertyCount >= $limit) {
            return redirect()->route('landlord.properties.index')
                ->with('error', "You have reached your listing limit of {$limit} properties. Upgrade your verification level to add more.");
        }

        return Inertia::render('Landlord/Properties/Create', [
            'verification_level' => $application->verification_level,
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

        // Determine visibility based on verification level
        $application = \App\Models\LandlordApplication::where('user_id', auth()->id())->first();
        
        // Basic tier listings are hidden by default (require manual approval)
        // Trusted/Premium are auto-visible
        $isVisible = $application && in_array($application->verification_level, ['trusted', 'premium']);

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
            'approval_status' => 'pending', // Always pending first? Or auto-approved? 
            // Stick to 'pending' for admin review of content, but 'is_visible_in_search' controls public search.
            // If we want trusted landlords to bypass "pending" status, we can change this.
            // For now, let's keep approval_status as 'pending' for content review, 
            // but set is_visible_in_search. actually if approval_status is pending it won't show anyway due to scope.
            // So let's make trusted landlords auto-approved?
            // "Existing properties -> Remain visible"
            // Let's set auto-approve for Trusted/Premium to reduce friction.
            'approval_status' => $isVisible ? 'approved' : 'pending',
            'is_visible_in_search' => $isVisible,
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
