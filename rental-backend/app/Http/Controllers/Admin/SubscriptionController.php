<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\User;
use App\Models\VerificationTier;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $tierType = $request->get('tier_type', 'landlord');

        $subscriptions = Subscription::with(['user', 'tier'])
            ->whereHas('tier', fn ($q) => $q->where('tier_type', $tierType))
            ->orderByDesc('created_at')
            ->paginate(25);

        $tiers = VerificationTier::where('tier_type', $tierType)->where('is_active', true)->get();

        $stats = [
            'active'    => Subscription::active()->whereHas('tier', fn ($q) => $q->where('tier_type', $tierType))->count(),
            'cancelled' => Subscription::where('status', 'cancelled')->whereHas('tier', fn ($q) => $q->where('tier_type', $tierType))->count(),
            'expired'   => Subscription::where('status', 'expired')->whereHas('tier', fn ($q) => $q->where('tier_type', $tierType))->count(),
        ];

        return Inertia::render('Admin/Subscriptions/Index', [
            'subscriptions' => $subscriptions,
            'tiers'         => $tiers,
            'stats'         => $stats,
            'tierType'      => $tierType,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'              => 'required|exists:users,id',
            'verification_tier_id' => 'required|exists:verification_tiers,id',
            'billing_cycle'        => 'required|in:free,monthly,annual',
            'ends_at'              => 'nullable|date|after:today',
            'notes'                => 'nullable|string|max:500',
        ]);

        // Cancel any existing active subscription for the same tier type
        $tier = VerificationTier::findOrFail($validated['verification_tier_id']);
        Subscription::where('user_id', $validated['user_id'])
            ->whereHas('tier', fn ($q) => $q->where('tier_type', $tier->tier_type))
            ->active()
            ->update(['status' => 'cancelled', 'cancelled_at' => now()]);

        Subscription::create(array_merge($validated, [
            'status'     => 'active',
            'starts_at'  => now(),
        ]));

        return redirect()->route('admin.subscriptions.index')
            ->with('success', 'Subscription created successfully.');
    }

    public function update(Request $request, Subscription $subscription)
    {
        $validated = $request->validate([
            'status'               => 'sometimes|in:active,cancelled,expired,trial',
            'verification_tier_id' => 'sometimes|exists:verification_tiers,id',
            'billing_cycle'        => 'sometimes|in:free,monthly,annual',
            'ends_at'              => 'nullable|date',
            'notes'                => 'nullable|string|max:500',
        ]);

        if (isset($validated['status']) && $validated['status'] === 'cancelled') {
            $validated['cancelled_at'] = now();
        }

        $subscription->update($validated);

        return back()->with('success', 'Subscription updated.');
    }

    public function destroy(Subscription $subscription)
    {
        $subscription->update(['status' => 'cancelled', 'cancelled_at' => now()]);

        return back()->with('success', 'Subscription cancelled.');
    }
}
