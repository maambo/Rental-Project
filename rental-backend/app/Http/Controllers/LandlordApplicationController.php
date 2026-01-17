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
        return Inertia::render('Landlord/Apply');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|max:20',
            'nrc_passport' => 'required|string|max:50|unique:landlord_applications,nrc_passport',
            'address' => 'required|string|max:255',
            'province' => 'required|string|max:100',
            'town' => 'required|string|max:100',
            'verification_level' => 'required|in:basic,trusted,premium',
            'landlord_type' => 'required|in:private_landlord,agent',
            // Using mimetypes alongside mimes for better Windows support
            'id_document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'proof_of_address' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'tax_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'selfie' => 'nullable|file|mimes:jpg,jpeg,png|max:5120', 
            'video_walkthrough' => 'nullable|file|mimes:mp4,mov,qt|max:20480',
            'business_registration' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        // Upload documents
        $idPath = $request->file('id_document')->store('landlord_docs', 'public');
        $addressPath = $request->file('proof_of_address')->store('landlord_docs', 'public');
        $taxPath = $request->hasFile('tax_certificate') 
            ? $request->file('tax_certificate')->store('landlord_docs', 'public') 
            : null;
        $selfiePath = $request->hasFile('selfie')
            ? $request->file('selfie')->store('landlord_docs', 'public')
            : null;
        $videoPath = $request->hasFile('video_walkthrough')
            ? $request->file('video_walkthrough')->store('landlord_docs', 'public')
            : null;
        $businessRegPath = $request->hasFile('business_registration')
            ? $request->file('business_registration')->store('landlord_docs', 'public')
            : null;

        // Determine listing limit
        $limit = 5; // Basic default
        if ($validated['verification_level'] === 'trusted') $limit = 999999; // Unlimited
        if ($validated['verification_level'] === 'premium') $limit = 999999; // Unlimited

        // Get the applicant_landlord role
        $applicantRole = DB::table('roles')->where('name', 'applicant_landlord')->first();
        
        // Create user account
        $user = \App\Models\User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => \Hash::make($validated['password']),
            'role_id' => $applicantRole ? $applicantRole->id : null,
        ]);

        // Create landlord application
        DB::table('landlord_applications')->insert([
            'user_id' => $user->id,
            'nrc_passport' => $validated['nrc_passport'],
            'address' => $validated['address'],
            'province' => $validated['province'],
            'town' => $validated['town'],
            'verification_level' => $validated['verification_level'],
            'landlord_type' => $validated['landlord_type'],
            'listing_limit' => $limit,
            'selfie_url' => $selfiePath,
            'video_walkthrough_url' => $videoPath,
            'business_registration_url' => $businessRegPath,
            'verification_submitted_at' => now(),
            'status' => 'pending',
            'id_document_url' => $idPath,
            'proof_of_address_url' => $addressPath,
            'tax_certificate_url' => $taxPath,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Log the user in
        auth()->login($user);

        return redirect()->route('landlord.status')->with('success', 'Landlord application submitted successfully! Your account has been created.');
    }

    public function status()
    {
        $application = DB::table('landlord_applications')
            ->where('user_id', auth()->id())
            ->first();

        if (!$application) {
            return redirect()->route('landlord.apply')->with('info', 'You have not submitted a landlord application yet.');
        }

        return Inertia::render('Landlord/ApplicationStatus', [
            'application' => $application,
        ]);
    }

    public function edit()
    {
        $application = DB::table('landlord_applications')
            ->where('user_id', auth()->id())
            ->first();

        if (!$application) {
            return redirect()->route('landlord.apply')->with('info', 'You have not submitted a landlord application yet.');
        }

        $user = auth()->user();

        return Inertia::render('Landlord/EditApplication', [
            'application' => $application,
            'user' => $user,
        ]);
    }

    public function update(Request $request)
    {
        $application = DB::table('landlord_applications')
            ->where('user_id', auth()->id())
            ->first();

        if (!$application) {
            return redirect()->route('landlord.apply')->with('error', 'Application not found.');
        }

        // Only allow editing if status is pending or rejected
        if (!in_array($application->status, ['pending', 'rejected'])) {
            return back()->with('error', 'You cannot edit an application that has been approved or is under review.');
        }

        $validated = $request->validate([
            'address' => 'required|string|max:255',
            'province' => 'required|string|max:100',
            'town' => 'required|string|max:100',
            'verification_level' => 'required|in:basic,trusted,premium',
            'landlord_type' => 'required|in:private_landlord,agent',
            'id_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'proof_of_address' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'tax_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'selfie' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            'video_walkthrough' => 'nullable|file|mimes:mp4,mov,qt|max:20480',
            'business_registration' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $limit = 5;
        if ($validated['verification_level'] === 'trusted') $limit = 999999;
        if ($validated['verification_level'] === 'premium') $limit = 999999;

        $updateData = [
            'address' => $validated['address'],
            'province' => $validated['province'],
            'town' => $validated['town'],
            'verification_level' => $validated['verification_level'],
            'landlord_type' => $validated['landlord_type'],
            'listing_limit' => $limit,
            'updated_at' => now(),
        ];

        // Update documents if new ones are uploaded
        if ($request->hasFile('id_document')) {
            // Delete old file
            if ($application->id_document_url) {
                Storage::disk('public')->delete($application->id_document_url);
            }
            $updateData['id_document_url'] = $request->file('id_document')->store('landlord_docs', 'public');
        }

        if ($request->hasFile('proof_of_address')) {
            if ($application->proof_of_address_url) {
                Storage::disk('public')->delete($application->proof_of_address_url);
            }
            $updateData['proof_of_address_url'] = $request->file('proof_of_address')->store('landlord_docs', 'public');
        }

        if ($request->hasFile('tax_certificate')) {
            if ($application->tax_certificate_url) {
                Storage::disk('public')->delete($application->tax_certificate_url);
            }
            $updateData['tax_certificate_url'] = $request->file('tax_certificate')->store('landlord_docs', 'public');
        }

        if ($request->hasFile('selfie')) {
            if ($application->selfie_url) {
                Storage::disk('public')->delete($application->selfie_url);
            }
            $updateData['selfie_url'] = $request->file('selfie')->store('landlord_docs', 'public');
        }

        if ($request->hasFile('video_walkthrough')) {
            if ($application->video_walkthrough_url) {
                Storage::disk('public')->delete($application->video_walkthrough_url);
            }
            $updateData['video_walkthrough_url'] = $request->file('video_walkthrough')->store('landlord_docs', 'public');
        }

        if ($request->hasFile('business_registration')) {
            if ($application->business_registration_url) {
                Storage::disk('public')->delete($application->business_registration_url);
            }
            $updateData['business_registration_url'] = $request->file('business_registration')->store('landlord_docs', 'public');
        }

        DB::table('landlord_applications')
            ->where('id', $application->id)
            ->update($updateData);

        return redirect()->route('landlord.status')->with('success', 'Application updated successfully.');
    }
}
