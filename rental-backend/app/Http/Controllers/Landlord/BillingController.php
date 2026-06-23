<?php

namespace App\Http\Controllers\Landlord;

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
     * Display a listing of bills for the landlord's tenants.
     */
    public function index()
    {
        $billings = Billing::whereHas('leaseAgreement', function ($query) {
            $query->where('landlord_id', auth()->id());
        })
        ->with(['user', 'leaseAgreement.property'])
        ->latest()
        ->paginate(15);

        return Inertia::render('Landlord/Billing/Index', [
            'billings' => $billings,
        ]);
    }

    /**
     * Verify a payment.
     */
    public function verifyPayment(Request $request, Billing $billing)
    {
        // Ensure landlord owns the lease
        if ($billing->leaseAgreement->landlord_id !== auth()->id()) {
            abort(403);
        }

        $billing->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        // Notify Tenant
        \Illuminate\Support\Facades\Mail::to($billing->user->email)
            ->send(new \App\Mail\PaymentVerifiedTenant(
                $billing->leaseAgreement->property->title,
                $billing->Amount,
                now()->toDateString()
            ));

        return back()->with('success', 'Payment verified successfully.');
    }
}
