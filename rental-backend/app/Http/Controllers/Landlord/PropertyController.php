<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Http\Requests\Property\StorePropertyRequest;
use App\Http\Requests\Property\UpdatePropertyRequest;
use App\Models\LandlordApplication;
use App\Models\Property;
use App\Models\UtilityType;
use App\Services\PropertyService;
use Inertia\Inertia;

class PropertyController extends Controller
{
    public function __construct(private PropertyService $propertyService) {}

    public function index()
    {
        $properties = Property::with(['images', 'province', 'district', 'town'])
            ->forLandlord(auth()->id())
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('Landlord/Properties/Index', [
            'properties' => $properties,
        ]);
    }

    public function create()
    {
        $application = LandlordApplication::forUser(auth()->id())->approved()->first();

        if (!$application) {
            return redirect()->route('dashboard')
                ->with('error', 'You must be an approved landlord to add properties.');
        }

        return Inertia::render('Landlord/Properties/Create', [
            'utilityTypes' => UtilityType::active()->with('options')->get(),
        ]);
    }

    public function store(StorePropertyRequest $request)
    {
        $property = Property::create(array_merge(
            $request->only([
                'title', 'description', 'price',
                'province_id', 'district_id', 'town_id',
                'street_address', 'latitude', 'longitude',
                'property_type', 'property_subtype', 'listing_type',
                'bedrooms', 'bathrooms', 'square_feet',
            ]),
            [
                'landlord_id'          => auth()->id(),
                'amenities'            => $request->amenities ?? [],
                'approval_status'      => 'pending',
                'is_visible_in_search' => false,
            ]
        ));

        $this->propertyService->storeImages($property, $request->file('images'));
        $this->propertyService->syncUtilities($property, $request->utilities ?? []);

        return redirect()->route('landlord.properties.index')
            ->with('success', 'Property submitted for review.');
    }

    public function edit($id)
    {
        $property = Property::with(['images', 'province', 'district', 'town', 'utilities.options'])
            ->forLandlord(auth()->id())
            ->findOrFail($id);

        if (!in_array($property->approval_status, ['pending', 'rejected'])) {
            return redirect()->route('landlord.properties.index')
                ->with('error', 'You can only edit properties that are pending or rejected.');
        }

        return Inertia::render('Landlord/Properties/Edit', [
            'property'     => $property,
            'utilityTypes' => UtilityType::active()->with('options')->get(),
        ]);
    }

    public function update(UpdatePropertyRequest $request, $id)
    {
        $property = Property::forLandlord(auth()->id())->findOrFail($id);

        if (!in_array($property->approval_status, ['pending', 'rejected'])) {
            return back()->with('error', 'You can only edit properties that are pending or rejected.');
        }

        $property->update(array_merge(
            $request->only([
                'title', 'description', 'price',
                'province_id', 'district_id', 'town_id',
                'street_address', 'latitude', 'longitude',
                'property_type', 'property_subtype', 'listing_type',
                'bedrooms', 'bathrooms', 'square_feet',
            ]),
            [
                'amenities'       => $request->amenities ?? [],
                'approval_status' => 'pending',
            ]
        ));

        if ($request->filled('remove_images')) {
            $this->propertyService->deleteImages($property, $request->remove_images);
        }

        if ($request->hasFile('new_images')) {
            $this->propertyService->storeImages($property, $request->file('new_images'));
        }

        $this->propertyService->syncUtilities($property, $request->utilities ?? []);

        return redirect()->route('landlord.properties.index')
            ->with('success', 'Property updated and resubmitted for review.');
    }

    public function destroy($id)
    {
        $property = Property::with('images')
            ->forLandlord(auth()->id())
            ->findOrFail($id);

        $this->propertyService->deleteProperty($property);

        return redirect()->route('landlord.properties.index')
            ->with('success', 'Property deleted successfully.');
    }
}
