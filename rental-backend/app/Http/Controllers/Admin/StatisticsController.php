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
            ],
            'properties' => [
                'total' => DB::table('properties')->count(),
                'approved' => DB::table('properties')->where('approval_status', 'approved')->count(),
                'pending' => DB::table('properties')->where('approval_status', 'pending')->count(),
                'rejected' => DB::table('properties')->where('approval_status', 'rejected')->count(),
                'total_views' => DB::table('properties')->sum('view_count'),
                'total_likes' => DB::table('properties')->sum('like_count'),
            ],
            'applications' => [
                'total' => DB::table('property_applications')->count(),
                'pending' => DB::table('property_applications')->where('status', 'pending')->count(),
                'approved' => DB::table('property_applications')->where('status', 'approved')->count(),
                'rejected' => DB::table('property_applications')->where('status', 'rejected')->count(),
            ],
            'financials' => [
                'total_transactions' => DB::table('transactions')->where('status', 'COMPLETED')->sum('Amount'),
                'avg_transaction' => DB::table('transactions')->where('status', 'COMPLETED')->avg('Amount') ?? 0,
            ],
        ];

        // Growth Chart Data (Last 6 Months)
        $growthData = DB::table('users')
            ->select('created_at')
            ->where('created_at', '>=', now()->subMonths(6))
            ->get()
            ->groupBy(function($date) {
                return \Carbon\Carbon::parse($date->created_at)->format('Y-m');
            })
            ->map(function($group, $month) {
                return [
                    'month' => $month,
                    'count' => $group->count(),
                ];
            })
            ->values()
            ->sortBy('month')
            ->values();

        // Recent Activity
        $recentTransactions = DB::table('transactions')
            ->join('users', 'transactions.UserID', '=', 'users.id')
            ->select('transactions.*', 'users.name as user_name')
            ->orderBy('transactions.created_at', 'desc')
            ->limit(5)
            ->get();

        return Inertia::render('Admin/Statistics/Index', [
            'stats' => $stats,
            'growthData' => $growthData,
            'recentTransactions' => $recentTransactions,
        ]);
    }
}
