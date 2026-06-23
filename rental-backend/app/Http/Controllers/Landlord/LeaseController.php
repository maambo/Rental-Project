<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LeaseController extends Controller
{
    /**
     * Show the form for creating a new lease.
     */
    public function create(Request $request)
    {
        $applicationId = $request->get('application_id');
        $application = \App\Models\PropertyApplication::with(['property', 'user'])
            ->findOrFail($applicationId);

        // Ensure landlord owns the property
        if ($application->property->landlord_id !== auth()->id()) {
            abort(403);
        }

        return Inertia::render('Landlord/Leases/Create', [
            'application' => $application,
            'property' => $application->property,
            'tenant' => $application->user,
        ]);
    }

    /**
     * Store a newly created lease.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'user_id' => 'required|exists:users,id',
            'monthly_rent' => 'required|numeric',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'content' => 'required|string',
        ]);

        $lease = \App\Models\LeaseAgreement::create([
            'property_id' => $validated['property_id'],
            'user_id' => $validated['user_id'],
            'landlord_id' => auth()->id(),
            'monthly_rent' => $validated['monthly_rent'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'content' => $validated['content'],
            'status' => 'pending_tenant_signature',
        ]);

        return redirect()->route('landlord.properties.index')
            ->with('success', 'Lease agreement created and sent to tenant for signature.');
    }

    /**
     * Display the specified lease for printing.
     */
    public function print(\App\Models\LeaseAgreement $lease)
    {
        // Ensure user is landlord or tenant
        if ($lease->landlord_id !== auth()->id() && $lease->user_id !== auth()->id()) {
            abort(403);
        }

        $lease->load(['property', 'tenant', 'landlord']);

        return view('landlord.leases.print', compact('lease'));
    }

    /**
     * Display the specified lease.
     */
    public function show(\App\Models\LeaseAgreement $lease)
    {
        // Ensure landlord owns the property
        if ($lease->landlord_id !== auth()->id()) {
            abort(403);
        }

        $lease->load(['property', 'tenant']);

        return Inertia::render('Landlord/Leases/Show', [
            'lease' => $lease,
        ]);
    }
}
