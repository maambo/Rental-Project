<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PropertyApprovalController extends Controller
{
    /**
     * Display properties pending approval.
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');
        
        $query = DB::table('properties')
            ->join('users', 'properties.landlord_id', '=', 'users.id')
            ->select(
                'properties.*',
                'users.name as landlord_name',
                'users.email as landlord_email'
            );

        if ($status !== 'all') {
            $query->where('properties.approval_status', $status);
        }

        $properties = $query->orderBy('properties.created_at', 'desc')->get();

        $stats = [
            'total' => DB::table('properties')->count(),
            'pending' => DB::table('properties')->where('approval_status', 'pending')->count(),
            'approved' => DB::table('properties')->where('approval_status', 'approved')->count(),
            'rejected' => DB::table('properties')->where('approval_status', 'rejected')->count(),
        ];

        return Inertia::render('Admin/Properties/Index', [
            'properties' => $properties,
            'stats' => $stats,
            'currentStatus' => $status,
        ]);
    }

    /**
     * Show property details.
     */
    public function show($id)
    {
        $property = \App\Models\Property::with(['images', 'landlord'])
            ->findOrFail($id);

        return Inertia::render('Admin/Properties/Show', [
            'property' => $property,
        ]);
    }

    /**
     * Approve property.
     */
    public function approve($id)
    {
        DB::table('properties')->where('id', $id)->update([
            'approval_status' => 'approved',
            'is_approved' => true,
            'approved_date' => now(),
        ]);

        return redirect()->route('admin.properties.index')->with('success', 'Property approved successfully.');
    }

    /**
     * Reject property.
     */
    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        DB::table('properties')->where('id', $id)->update([
            'approval_status' => 'rejected',
            'is_approved' => false,
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return redirect()->route('admin.properties.index')->with('success', 'Property rejected.');
    }
}
