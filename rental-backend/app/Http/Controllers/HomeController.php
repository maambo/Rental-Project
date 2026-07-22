<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\Property;
use App\Models\Province;
use App\Models\Town;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        $totalVisible = Property::visibleInSearch()->count();

        $properties = Property::with(['images', 'province', 'district', 'town'])
            ->visibleInSearch()
            ->latest()
            ->take(6)
            ->get();

        $provinces = Province::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Welcome', [
            'canLogin'      => true,
            'canRegister'   => true,
            'properties'    => $properties,
            'totalVisible'  => $totalVisible,
            'provinces'     => $provinces,
        ]);
    }

    public function browse(Request $request)
    {
        $query = Property::with(['images', 'province', 'district', 'town'])
            ->visibleInSearch();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('province_id')) {
            $query->where('province_id', $request->province_id);
        }

        if ($request->filled('district_id')) {
            $query->where('district_id', $request->district_id);
        }

        if ($request->filled('town_id')) {
            $query->where('town_id', $request->town_id);
        }

        if ($request->filled('property_type')) {
            $query->where('property_type', $request->property_type);
        }

        if ($request->filled('listing_type')) {
            $query->where('listing_type', $request->listing_type);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->filled('bedrooms')) {
            $query->where('bedrooms', '>=', $request->bedrooms);
        }

        $properties = $query->latest()->paginate(15)->withQueryString();
        $provinces  = Province::orderBy('name')->get(['id', 'name']);

        $districts = $request->filled('province_id')
            ? District::where('province_id', $request->province_id)->orderBy('name')->get(['id', 'name'])
            : collect();

        $towns = $request->filled('district_id')
            ? Town::where('district_id', $request->district_id)->orderBy('name')->get(['id', 'name'])
            : collect();

        // Unpaginated marker data for the map view (only what the markers need)
        $mapProperties = (clone $query)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get(['id', 'title', 'price', 'latitude', 'longitude', 'property_type', 'listing_type']);

        return Inertia::render('Properties/Index', [
            'properties'    => $properties,
            'mapProperties' => $mapProperties,
            'provinces'     => $provinces,
            'districts'     => $districts,
            'towns'         => $towns,
            'filters'       => $request->only(['search', 'province_id', 'district_id', 'town_id', 'property_type', 'listing_type', 'min_price', 'max_price', 'bedrooms']),
        ]);
    }
}
