<?php

namespace App\Http\Controllers\Payments\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Billing;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BillingController extends Controller
{
    public function __construct(private readonly PaymentService $paymentService) {}

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
     * Confirm payment for a bill (manual / offline proof-of-payment upload).
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

    /**
     * Pay a bill in-app via a simulated payment method (mobile money / card).
     */
    public function payViaSimulatedMethod(Request $request, Billing $billing)
    {
        if ($billing->UserID != auth()->id()) {
            abort(403);
        }

        if ($billing->status === 'paid') {
            return back()->with('error', 'This bill has already been paid.');
        }

        $this->paymentService->initiateBillingPayment($billing, $request->only([
            'method', 'provider', 'phone', 'card_number', 'card_expiry', 'card_cvv', 'cardholder_name',
        ]));

        return back()->with('success', 'Payment successful — this bill is now marked as paid.');
    }
}
