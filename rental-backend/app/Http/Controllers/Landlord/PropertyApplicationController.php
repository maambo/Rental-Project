<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Mail\ApplicationStatusTenant;
use App\Mail\PaymentRequestedTenant;
use App\Models\PropertyApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class PropertyApplicationController extends Controller
{
    public function index()
    {
        $applications = PropertyApplication::whereHas('property', fn ($q) => $q->where('landlord_id', auth()->id()))
            ->with(['property', 'user'])
            ->latest()
            ->paginate(20);

        return Inertia::render('Landlord/Applications/Index', [
            'applications' => $applications,
        ]);
    }

    public function show(PropertyApplication $propertyApplication)
    {
        if ($propertyApplication->property->landlord_id !== auth()->id()) abort(403);

        $propertyApplication->load(['property.province', 'property.district', 'property.town', 'user']);

        return Inertia::render('Landlord/Applications/Show', [
            'application' => $propertyApplication,
        ]);
    }

    /** Start the review process. */
    public function startReview(PropertyApplication $application)
    {
        if ($application->property->landlord_id !== auth()->id()) abort(403);

        if ($application->status !== 'pending') {
            return back()->with('error', 'Can only start review on a pending application.');
        }

        $application->update(['status' => 'under_review', 'review_started_at' => now()]);

        Mail::to($application->user->email)
            ->send(new ApplicationStatusTenant($application->user, $application->property->title, 'under_review'));

        return back()->with('success', 'Review started. The applicant has been notified.');
    }

    /** Landlord is happy — request payment from the tenant. */
    public function requestPayment(Request $request, PropertyApplication $application)
    {
        if ($application->property->landlord_id !== auth()->id()) abort(403);

        if ($application->status !== 'under_review') {
            return back()->with('error', 'Application must be under review before requesting payment.');
        }

        $rules = ['deadline_hours' => 'required|integer|min:1|max:168'];

        if ($application->applicant_terms) {
            $rules['landlord_agreed_terms'] = 'accepted';
        }

        $request->validate($rules, [
            'landlord_agreed_terms.accepted' => 'You must agree to the applicant\'s terms and conditions before requesting payment.',
        ]);

        // Block if another application for this property already has payment requested
        $alreadyRequested = PropertyApplication::where('property_id', $application->property_id)
            ->where('id', '!=', $application->id)
            ->where('status', 'payment_requested')
            ->exists();

        if ($alreadyRequested) {
            return back()->with('error', 'Payment is already requested for another applicant on this property.');
        }

        $deadline = now()->addHours((int) $request->deadline_hours);

        $application->update([
            'status'                   => 'payment_requested',
            'payment_requested_at'     => now(),
            'payment_deadline'         => $deadline,
            'landlord_agreed_terms_at' => $application->applicant_terms ? now() : null,
        ]);

        $application->load(['property.province', 'property.district', 'property.town', 'user']);

        Mail::to($application->user->email)->send(new PaymentRequestedTenant($application));

        return back()->with('success', 'Payment request sent. The applicant has been emailed.');
    }

    /** Reject an application. */
    public function reject(Request $request, PropertyApplication $application)
    {
        if ($application->property->landlord_id !== auth()->id()) abort(403);

        $request->validate(['reason' => 'nullable|string|max:500']);

        $application->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->reason,
        ]);

        Mail::to($application->user->email)
            ->send(new ApplicationStatusTenant($application->user, $application->property->title, 'rejected', $request->reason));

        return back()->with('success', 'Application rejected.');
    }
}
