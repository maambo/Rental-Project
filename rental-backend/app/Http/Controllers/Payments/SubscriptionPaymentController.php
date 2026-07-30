<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\VerificationTier;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubscriptionPaymentController extends Controller
{
    public function __construct(private readonly PaymentService $paymentService) {}

    /**
     * List the tiers available to the current user's role so they can pick one to subscribe to.
     */
    public function index(Request $request)
    {
        $tierType = $request->user()->isLandlord() ? 'landlord' : 'tenant';

        return Inertia::render('Payments/Subscribe/Index', [
            'tiers' => VerificationTier::where('tier_type', $tierType)
                ->where('is_active', true)
                ->orderBy('price_amount')
                ->get(),
        ]);
    }

    /**
     * Show the checkout page for a tier — pricing plus the simulated payment method picker.
     */
    public function create(VerificationTier $tier)
    {
        if (! $tier->is_active) {
            abort(404);
        }

        return Inertia::render('Payments/Subscribe/Checkout', [
            'tier' => $tier,
        ]);
    }

    /**
     * Charge the chosen (simulated) payment method and activate the subscription.
     */
    public function store(Request $request, VerificationTier $tier)
    {
        if (! $tier->is_active) {
            abort(404);
        }

        $validated = $request->validate([
            'billing_cycle' => 'required|in:free,monthly,annual',
        ]);

        $this->paymentService->initiateSubscriptionPayment(
            $request->user(),
            $tier,
            $validated['billing_cycle'],
            $request->only([
                'method', 'provider', 'phone', 'card_number', 'card_expiry', 'card_cvv', 'cardholder_name',
            ]),
        );

        return redirect()->route('dashboard')
            ->with('success', "Subscribed to {$tier->display_name}!");
    }
}
