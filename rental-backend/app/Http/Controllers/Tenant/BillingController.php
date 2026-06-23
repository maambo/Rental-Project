<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Billing;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BillingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * Display a listing of bills for the tenant.
     */
    public function index()
    {
        $billings = Billing::where('UserID', auth()->id())
            ->with(['leaseAgreement.property'])
            ->latest()
            ->paginate(15);

        return Inertia::render('Tenant/Billing/Index', [
            'billings' => $billings,
        ]);
    }

    /**
     * Confirm payment for a bill.
     */
    public function confirmPayment(Request $request, Billing $billing)
    {
        // Ensure billing belongs to tenant
        if ($billing->UserID != auth()->id()) {
            abort(403);
        }

        $request->validate([
            'proof_of_payment' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($request->hasFile('proof_of_payment')) {
            $path = $request->file('proof_of_payment')->store('proofs', 'public');
            $billing->update([
                'proof_of_payment' => $path,
                'status' => 'pending', // or 'processing'
            ]);
        }

        return back()->with('success', 'Payment proof submitted. Awaiting landlord verification.');
    }
}
