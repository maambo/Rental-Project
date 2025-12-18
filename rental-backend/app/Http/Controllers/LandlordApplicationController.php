<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use App\Models\LandlordApplication; // Ensure model exists
use Illuminate\Support\Facades\DB;

class LandlordApplicationController extends Controller
{
    public function create()
    {
        // Check if user already has a pending application
        $existing = DB::table('landlord_applications')
            ->where('user_id', auth()->id())
            ->whereIn('status', ['pending', 'under_review'])
            ->first();

        if ($existing) {
            return redirect()->route('dashboard')->with('error', 'You already have a pending application.');
        }

        return Inertia::render('Landlord/Apply');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tier' => 'required|in:small,medium,large',
            'id_document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'proof_of_address' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'tax_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $idPath = $request->file('id_document')->store('landlord_docs', 'public');
        $addressPath = $request->file('proof_of_address')->store('landlord_docs', 'public');
        $taxPath = $request->hasFile('tax_certificate') 
            ? $request->file('tax_certificate')->store('landlord_docs', 'public') 
            : null;

        DB::table('landlord_applications')->insert([
            'user_id' => auth()->id(),
            'tier' => $validated['tier'],
            'status' => 'pending',
            'id_document_url' => $idPath,
            'proof_of_address_url' => $addressPath,
            'tax_certificate_url' => $taxPath,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('dashboard')->with('success', 'Landlord application submitted successfully!');
    }
}
