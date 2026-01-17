<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class LandlordProfileController extends Controller
{
    /**
     * Display landlord profiles.
     */
    public function index()
    {
        $landlords = DB::table('users')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->leftJoin('landlord_applications', 'users.id', '=', 'landlord_applications.user_id')
            ->where('roles.name', 'landlord')
            ->select(
                'users.*',
                'landlord_applications.verification_level',
                'landlord_applications.status as application_status'
            )
            ->orderBy('users.created_at', 'desc')
            ->get();

        // Get property counts for each landlord
        foreach ($landlords as $landlord) {
            $landlord->property_count = DB::table('properties')
                ->where('landlord_id', $landlord->id)
                ->count();
        }

        return Inertia::render('Admin/Landlords/Index', [
            'landlords' => $landlords,
        ]);
    }

    /**
     * Display the specified landlord profile.
     */
    public function show($id)
    {
        $landlord = DB::table('users')
            ->leftJoin('landlord_applications', 'users.id', '=', 'landlord_applications.user_id')
            ->select(
                'users.*',
                'landlord_applications.verification_level',
                'landlord_applications.status as application_status',
                'landlord_applications.landlord_type',
                'landlord_applications.id_document_url',
                'landlord_applications.selfie_url',
                'landlord_applications.business_registration_url',
                'landlord_applications.video_walkthrough_url'
            )
            ->where('users.id', $id)
            ->first();

        if (!$landlord) {
            abort(404);
        }

        $properties = DB::table('properties')
            ->where('landlord_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Admin/Landlords/Show', [
            'landlord' => $landlord,
            'properties' => $properties,
        ]);
    }
}
