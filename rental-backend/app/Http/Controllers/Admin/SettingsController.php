<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VerificationTier;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingsController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index()
    {
        $tiers = VerificationTier::orderBy('price_amount', 'asc')->get();

        return Inertia::render('Admin/Settings/Index', [
            'tiers' => $tiers
        ]);
    }

    /**
     * Update the settings (tiers).
     */
    public function update(Request $request)
    {
        $request->validate([
            'tiers' => 'required|array',
            'tiers.*.id' => 'required|exists:verification_tiers,id',
            'tiers.*.display_name' => 'required|string',
            'tiers.*.price_display' => 'required|string',
            'tiers.*.price_amount' => 'required|integer',
            'tiers.*.property_limit' => 'required|integer',
            'tiers.*.features' => 'required|array',
        ]);

        foreach ($request->tiers as $tierData) {
            $tier = VerificationTier::find($tierData['id']);
            $tier->update([
                'display_name' => $tierData['display_name'],
                'price_display' => $tierData['price_display'],
                'price_amount' => $tierData['price_amount'],
                'property_limit' => $tierData['property_limit'],
                'features' => $tierData['features'],
                // Styling is not editable from UI for now to strictly maintain brand design
            ]);
        }

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }
}
