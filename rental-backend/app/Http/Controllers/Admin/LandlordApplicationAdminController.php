<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class LandlordApplicationAdminController extends Controller
{
    /**
     * Display landlord applications.
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');
        
        $query = DB::table('landlord_applications')
            ->join('users', 'landlord_applications.user_id', '=', 'users.id')
            ->select(
                'landlord_applications.*',
                'users.name as user_name',
                'users.email as user_email'
            );

        if ($status !== 'all') {
            $query->where('landlord_applications.status', $status);
        }

        $applications = $query->orderBy('landlord_applications.created_at', 'desc')->get();

        $stats = [
            'total' => DB::table('landlord_applications')->count(),
            'pending' => DB::table('landlord_applications')->where('status', 'pending')->count(),
            'under_review' => DB::table('landlord_applications')->where('status', 'under_review')->count(),
            'approved' => DB::table('landlord_applications')->where('status', 'approved')->count(),
            'rejected' => DB::table('landlord_applications')->where('status', 'rejected')->count(),
        ];

        return Inertia::render('Admin/Applications/Index', [
            'applications' => $applications,
            'stats' => $stats,
            'currentStatus' => $status,
        ]);
    }

    /**
     * Show application details.
     */
    public function show($id)
    {
        $application = DB::table('landlord_applications')
            ->join('users', 'landlord_applications.user_id', '=', 'users.id')
            ->select(
                'landlord_applications.*',
                'users.name as user_name',
                'users.email as user_email'
            )
            ->where('landlord_applications.id', $id)
            ->first();

        if (!$application) {
            return redirect()->route('admin.applications.index')->with('error', 'Application not found.');
        }

        return Inertia::render('Admin/Applications/Show', [
            'application' => $application,
        ]);
    }

    /**
     * Mark application as under review.
     */
    public function markAsUnderReview($id)
    {
        DB::table('landlord_applications')->where('id', $id)->update([
            'status' => 'under_review',
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Application marked as under review.');
    }

    /**
     * Approve landlord application.
     */
    public function approve(Request $request, $id)
    {
        $application = DB::table('landlord_applications')->where('id', $id)->first();
        
        if (!$application) {
            return back()->with('error', 'Application not found.');
        }

        // Update application status
        DB::table('landlord_applications')->where('id', $id)->update([
            'status' => 'approved',
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        // Assign landlord role to user
        $landlordRole = DB::table('roles')->where('name', 'landlord')->first();
        if ($landlordRole) {
            DB::table('users')->where('id', $application->user_id)->update([
                'role_id' => $landlordRole->id,
            ]);
        }

        return redirect()->route('admin.applications.index')->with('success', 'Application approved successfully. User is now a landlord.');
    }

    /**
     * Reject landlord application.
     */
    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        DB::table('landlord_applications')->where('id', $id)->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        return redirect()->route('admin.applications.index')->with('success', 'Application rejected.');
    }
}
