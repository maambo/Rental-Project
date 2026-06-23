<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Models\PropertyApplication;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PropertyApplicationController extends Controller
{
    /**
     * Display a listing of applications for the landlord's properties.
     */
    public function index()
    {
        $applications = PropertyApplication::whereHas('property', function ($query) {
            $query->where('landlord_id', auth()->id());
        })
        ->with(['property', 'user'])
        ->latest()
        ->paginate(15);

        return Inertia::render('Landlord/Applications/Index', [
            'applications' => $applications,
        ]);
    }

    /**
     * Display the specified application.
     */
    public function show(PropertyApplication $propertyApplication)
    {
        // Ensure landlord owns the property
        if ($propertyApplication->property->landlord_id !== auth()->id()) {
            abort(403);
        }

        $propertyApplication->load(['property', 'user']);

        return Inertia::render('Landlord/Applications/Show', [
            'application' => $propertyApplication,
        ]);
    }

    /**
     * Approve an application.
     */
    public function approve(PropertyApplication $application)
    {
        // Ensure landlord owns the property
        if ($application->property->landlord_id !== auth()->id()) {
            abort(403);
        }

        DB::beginTransaction();
        try {
            // SINGLE APPROVAL CONSTRAINT
            // Check if there's already an approved application for this property
            $hasApproved = PropertyApplication::where('property_id', $application->property_id)
                ->where('status', 'approved')
                ->exists();

            if ($hasApproved) {
                return back()->with('error', 'This property already has an approved application.');
            }

            // Update status
            $application->update(['status' => 'approved']);

            // Optionally: Auto-reject other pending applications?
            // "Update approval logic to allow only one active approval per property"
            // For now, let's just block the second approval.

            // Send Notification (Created in Phase 1)
            Mail::to($application->user->email)
                ->send(new \App\Mail\ApplicationStatusTenant($application->user, $application->property->title, 'approved'));

            DB::commit();
            return back()->with('success', 'Application approved successfully and tenant notified.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'An error occurred while approving the application.');
        }
    }

    /**
     * Reject an application.
     */
    public function reject(Request $request, PropertyApplication $application)
    {
        // Ensure landlord owns the property
        if ($application->property->landlord_id !== auth()->id()) {
            abort(403);
        }

        $application->update(['status' => 'rejected']);

        // Send Notification
        Mail::to($application->user->email)
            ->send(new \App\Mail\ApplicationStatusTenant($application->user, $application->property->title, 'rejected'));

        return back()->with('success', 'Application rejected.');
    }
}
