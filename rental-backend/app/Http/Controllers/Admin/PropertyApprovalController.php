<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Province;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PropertyApprovalController extends Controller
{
    public function index(Request $request)
    {
        $status       = $request->get('status', 'pending');
        $search       = $request->get('search');
        $provinceId   = $request->get('province_id');
        $propertyType = $request->get('property_type');
        $listingType  = $request->get('listing_type');

        $query = Property::with(['landlord', 'province', 'district', 'town'])
            ->orderByDesc('created_at');

        if ($status !== 'all') {
            $query->where('approval_status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhereHas('landlord', fn ($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        if ($provinceId) {
            $query->where('province_id', $provinceId);
        }

        if ($propertyType) {
            $query->where('property_type', $propertyType);
        }

        if ($listingType) {
            $query->where('listing_type', $listingType);
        }

        $properties = $query->get()->map(fn ($p) => [
            'id'              => $p->id,
            'code'            => $p->code,
            'title'           => $p->title,
            'landlord_name'   => $p->landlord?->name,
            'price'           => $p->price,
            'approval_status' => $p->approval_status,
            'property_type'       => $p->property_type,
            'listing_type'        => $p->listing_type,
            'availability_status' => $p->availability_status,
            'province'        => $p->province?->name,
            'district'        => $p->district?->name,
            'town'            => $p->town?->name,
            'created_at'      => $p->created_at,
        ]);

        $stats = [
            'total'    => Property::count(),
            'pending'  => Property::pending()->count(),
            'approved' => Property::approved()->count(),
            'rejected' => Property::where('approval_status', 'rejected')->count(),
        ];

        $provinces = Province::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Properties/Index', [
            'properties'    => $properties,
            'stats'         => $stats,
            'currentStatus' => $status,
            'provinces'     => $provinces,
            'filters'       => compact('search', 'provinceId', 'propertyType', 'listingType'),
        ]);
    }

    public function show(Property $property)
    {
        $property->load(['images', 'landlord', 'province', 'district', 'town', 'utilities.options', 'applications', 'documents.uploader']);

        return Inertia::render('Admin/Properties/Show', [
            'property' => $property,
        ]);
    }

    public function approve(Property $property)
    {
        $property->update([
            'approval_status'      => 'approved',
            'is_visible_in_search' => true,
            'approved_date'        => now(),
        ]);

        return redirect()->route('admin.properties.index')
            ->with('success', 'Property approved successfully.');
    }

    public function reject(Request $request, Property $property)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $property->update([
            'approval_status'      => 'rejected',
            'is_visible_in_search' => false,
            'rejection_reason'     => $validated['rejection_reason'],
        ]);

        return redirect()->route('admin.properties.index')
            ->with('success', 'Property rejected.');
    }
}
