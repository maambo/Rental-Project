<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $landlordId = auth()->id();

        // Get statistics
        $stats = [
            'total_properties' => DB::table('properties')
                ->where('landlord_id', $landlordId)
                ->count(),
            
            'approved_properties' => DB::table('properties')
                ->where('landlord_id', $landlordId)
                ->where('approval_status', 'approved')
                ->count(),
            
            'pending_properties' => DB::table('properties')
                ->where('landlord_id', $landlordId)
                ->where('approval_status', 'pending')
                ->count(),
            
            'total_applications' => DB::table('property_applications')
                ->join('properties', 'property_applications.property_id', '=', 'properties.id')
                ->where('properties.landlord_id', $landlordId)
                ->count(),
            
            'pending_applications' => DB::table('property_applications')
                ->join('properties', 'property_applications.property_id', '=', 'properties.id')
                ->where('properties.landlord_id', $landlordId)
                ->where('property_applications.status', 'pending')
                ->count(),
            
            'tour_requests' => DB::table('tour_requests')
                ->join('properties', 'tour_requests.property_id', '=', 'properties.id')
                ->where('properties.landlord_id', $landlordId)
                ->where('tour_requests.status', 'pending')
                ->count(),
        ];

        // Get recent properties
        $recentProperties = DB::table('properties')
            ->where('landlord_id', $landlordId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get landlord tier info
        $landlordApplication = DB::table('landlord_applications')
            ->where('user_id', $landlordId)
            ->where('status', 'approved')
            ->first();

        $tierLimits = [
            'small' => 10,
            'medium' => 50,
            'large' => null, // unlimited
        ];

        $tier = $landlordApplication ? $landlordApplication->tier : 'small';
        $propertyLimit = $tierLimits[$tier];

        return Inertia::render('Landlord/Dashboard', [
            'stats' => $stats,
            'recentProperties' => $recentProperties,
            'tier' => $tier,
            'propertyLimit' => $propertyLimit,
        ]);
    }
}
