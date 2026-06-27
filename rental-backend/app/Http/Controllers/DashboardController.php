<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use App\Models\LandlordApplication;
use App\Models\MaintenancePlan;
use App\Models\Message;
use App\Models\RentalHistory;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user()->load('roleModel');

        if ($user->hasRole('applicant_landlord')) {
            return $this->applicantLandlordDashboard($user);
        }

        if ($user->isLandlord()) {
            return redirect()->route('landlord.dashboard');
        }

        return $this->tenantDashboard($user);
    }

    private function applicantLandlordDashboard($user)
    {
        $application = LandlordApplication::forUser($user->id)->first();

        return Inertia::render('ApplicantLandlord/Dashboard', [
            'application' => $application,
        ]);
    }

    private function tenantDashboard($user)
    {
        $tenantStats = [
            'activeRentals'       => RentalHistory::where('tenant_id', $user->id)->active()->count(),
            'unreadMessages'      => Message::where('receiver_id', $user->id)->where('is_read', false)->count(),
            'maintenanceRequests' => MaintenancePlan::whereHas('property', fn($q) =>
                $q->whereHas('rentalHistories', fn($sq) =>
                    $sq->where('tenant_id', $user->id)->active()
                )
            )->where('status', 'scheduled')->count(),
            'rentDue' => Billing::where('UserID', $user->id)
                ->where('status', '!=', 'paid')
                ->sum('Amount'),
        ];

        return Inertia::render('Dashboard', [
            'tenantStats' => $tenantStats,
        ]);
    }
}
