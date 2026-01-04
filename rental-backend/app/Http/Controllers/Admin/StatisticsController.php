<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StatisticsController extends Controller
{
    /**
     * Display statistics dashboard.
     */
    public function index()
    {
        $stats = [
            'users' => [
                'total' => DB::table('users')->count(),
                'admins' => DB::table('users')->join('roles', 'users.role_id', '=', 'roles.id')->where('roles.name', 'admin')->count(),
                'landlords' => DB::table('users')->join('roles', 'users.role_id', '=', 'roles.id')->where('roles.name', 'landlord')->count(),
                'tenants' => DB::table('users')->join('roles', 'users.role_id', '=', 'roles.id')->where('roles.name', 'tenant')->count(),
                'applicants' => DB::table('users')->join('roles', 'users.role_id', '=', 'roles.id')->where('roles.name', 'applicant_landlord')->count(),
            ],
            'properties' => [
                'total' => DB::table('properties')->count(),
                'available' => DB::table('properties')->where('approval_status', 'approved')->count(),
                'rented' => 0, // Not tracked in current schema
                'maintenance' => 0, // Not tracked in current schema
            ],
            'applications' => [
                'total' => DB::table('landlord_applications')->count(),
                'pending' => DB::table('landlord_applications')->where('status', 'pending')->count(),
                'approved' => DB::table('landlord_applications')->where('status', 'approved')->count(),
                'rejected' => DB::table('landlord_applications')->where('status', 'rejected')->count(),
            ],
            'revenue' => [
                'total_properties' => DB::table('properties')->count(),
                'avg_price' => DB::table('properties')->avg('price') ?? 0,
            ],
        ];

        // Recent activity
        $recentApplications = DB::table('landlord_applications')
            ->join('users', 'landlord_applications.user_id', '=', 'users.id')
            ->select('landlord_applications.*', 'users.name as user_name')
            ->orderBy('landlord_applications.created_at', 'desc')
            ->limit(5)
            ->get();

        return Inertia::render('Admin/Statistics/Index', [
            'stats' => $stats,
            'recentApplications' => $recentApplications,
        ]);
    }
}
