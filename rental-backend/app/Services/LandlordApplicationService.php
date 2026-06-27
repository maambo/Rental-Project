<?php

namespace App\Services;

use App\Models\LandlordApplication;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class LandlordApplicationService
{
    /**
     * Store all uploaded documents and return a map of column => storage path.
     */
    public function storeDocuments(Request $request): array
    {
        $fields = [
            'id_document'           => 'id_document_url',
            'proof_of_address'      => 'proof_of_address_url',
            'tax_certificate'       => 'tax_certificate_url',
            'selfie'                => 'selfie_url',
            'video_walkthrough'     => 'video_walkthrough_url',
            'business_registration' => 'business_registration_url',
        ];

        $docs = [];
        foreach ($fields as $input => $column) {
            if ($request->hasFile($input)) {
                $docs[$column] = $request->file($input)->store('landlord_docs', 'public');
            }
        }

        return $docs;
    }

    /**
     * Replace documents on an existing application, deleting old files.
     */
    public function replaceDocuments(Request $request, LandlordApplication $application): array
    {
        $fields = [
            'id_document'           => ['column' => 'id_document_url',           'old' => $application->id_document_url],
            'proof_of_address'      => ['column' => 'proof_of_address_url',      'old' => $application->proof_of_address_url],
            'tax_certificate'       => ['column' => 'tax_certificate_url',       'old' => $application->tax_certificate_url],
            'selfie'                => ['column' => 'selfie_url',                'old' => $application->selfie_url],
            'video_walkthrough'     => ['column' => 'video_walkthrough_url',     'old' => $application->video_walkthrough_url],
            'business_registration' => ['column' => 'business_registration_url', 'old' => $application->business_registration_url],
        ];

        $docs = [];
        foreach ($fields as $input => $meta) {
            if ($request->hasFile($input)) {
                if ($meta['old']) {
                    Storage::disk('public')->delete($meta['old']);
                }
                $docs[$meta['column']] = $request->file($input)->store('landlord_docs', 'public');
            }
        }

        return $docs;
    }

    /**
     * Create a new user + landlord application from validated data and uploaded documents.
     */
    public function createUserAndApplication(array $validated, array $documents): LandlordApplication
    {
        $role = Role::where('name', 'applicant_landlord')->first();

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id'  => $role?->id,
        ]);

        return LandlordApplication::create(array_merge([
            'user_id'                   => $user->id,
            'nrc_passport'              => $validated['nrc_passport'],
            'address'                   => $validated['address'],
            'province'                  => $validated['province'],
            'town'                      => $validated['town'],
            'landlord_type'             => $validated['landlord_type'],
            'status'                    => 'pending',
            'verification_submitted_at' => now(),
        ], $documents));
    }

    /**
     * Promote a user to the landlord role after application approval.
     */
    public function promoteToLandlord(User $user): void
    {
        $landlordRole = Role::where('name', 'landlord')->firstOrFail();
        $user->update(['role_id' => $landlordRole->id]);
    }
}
