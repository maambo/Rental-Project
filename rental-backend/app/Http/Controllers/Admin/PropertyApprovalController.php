<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PropertyApprovalController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');

        $query = Property::with('landlord')->orderByDesc('created_at');

        if ($status !== 'all') {
            $query->where('approval_status', $status);
        }

        $properties = $query->get();

        $stats = [
            'total'    => Property::count(),
            'pending'  => Property::pending()->count(),
            'approved' => Property::approved()->count(),
            'rejected' => Property::where('approval_status', 'rejected')->count(),
        ];

        return Inertia::render('Admin/Properties/Index', [
            'properties'    => $properties,
            'stats'         => $stats,
            'currentStatus' => $status,
        ]);
    }

    public function show($id)
    {
        $property = Property::with(['images', 'landlord'])->findOrFail($id);

        return Inertia::render('Admin/Properties/Show', [
            'property' => $property,
        ]);
    }

    public function approve($id)
    {
        Property::findOrFail($id)->update([
            'approval_status'     => 'approved',
            'is_visible_in_search'=> true,
            'approved_date'       => now(),
        ]);

        return redirect()->route('admin.properties.index')
            ->with('success', 'Property approved successfully.');
    }

    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        Property::findOrFail($id)->update([
            'approval_status'     => 'rejected',
            'is_visible_in_search'=> false,
            'rejection_reason'    => $validated['rejection_reason'],
        ]);

        return redirect()->route('admin.properties.index')
            ->with('success', 'Property rejected.');
    }
}
