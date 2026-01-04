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
                'landlord_applications.tier',
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
}
