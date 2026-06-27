<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyApplication;
use App\Models\TourRequest;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $landlordId = auth()->id();
        $propertyIds = Property::forLandlord($landlordId)->pluck('id');

        $stats = [
            'total_properties'     => $propertyIds->count(),
            'approved_properties'  => Property::forLandlord($landlordId)->approved()->count(),
            'pending_properties'   => Property::forLandlord($landlordId)->pending()->count(),
            'total_applications'   => PropertyApplication::whereIn('property_id', $propertyIds)->count(),
            'pending_applications' => PropertyApplication::whereIn('property_id', $propertyIds)->where('status', 'pending')->count(),
            'tour_requests'        => TourRequest::whereIn('property_id', $propertyIds)->where('status', 'pending')->count(),
        ];

        $recentProperties = Property::forLandlord($landlordId)
            ->with('images')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return Inertia::render('Landlord/Dashboard', [
            'stats'            => $stats,
            'recentProperties' => $recentProperties,
        ]);
    }
}
