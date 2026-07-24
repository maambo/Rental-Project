<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user    = $request->user();
        $profile = $user->workerProfile;

        if (!$profile) {
            return redirect()->route('worker.profile.create');
        }

        $profile->load(['category', 'town', 'allServices']);

        $stats = [
            'pending_bookings'   => $profile->bookings()->pending()->count(),
            'active_bookings'    => $profile->bookings()->active()->count(),
            'completed_bookings' => $profile->bookings()->completed()->count(),
            'total_reviews'      => $profile->rating_count,
            'average_rating'     => $profile->rating_average,
            'portfolio_photos'   => $profile->portfolioPhotos()->count(),
        ];

        return Inertia::render('Worker/Dashboard', [
            'profile'          => $profile,
            'stats'            => $stats,
            'recentBookings'   => $profile->bookings()->with(['client', 'workerService'])->latest()->limit(5)->get(),
        ]);
    }
}
