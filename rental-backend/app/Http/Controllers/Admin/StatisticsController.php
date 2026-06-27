<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyApplication;
use App\Models\Transaction;
use App\Models\User;
use Inertia\Inertia;

class StatisticsController extends Controller
{
    public function index()
    {
        $stats = [
            'users' => [
                'total'     => User::count(),
                'admins'    => User::whereHas('roleModel', fn($q) => $q->where('name', 'admin'))->count(),
                'landlords' => User::whereHas('roleModel', fn($q) => $q->where('name', 'landlord'))->count(),
                'tenants'   => User::whereHas('roleModel', fn($q) => $q->where('name', 'tenant'))->count(),
            ],
            'properties' => [
                'total'       => Property::count(),
                'approved'    => Property::approved()->count(),
                'pending'     => Property::pending()->count(),
                'rejected'    => Property::where('approval_status', 'rejected')->count(),
                'total_views' => Property::sum('view_count'),
                'total_likes' => Property::sum('like_count'),
            ],
            'applications' => [
                'total'    => PropertyApplication::count(),
                'pending'  => PropertyApplication::where('status', 'pending')->count(),
                'approved' => PropertyApplication::where('status', 'approved')->count(),
                'rejected' => PropertyApplication::where('status', 'rejected')->count(),
            ],
            'financials' => [
                'total_transactions' => Transaction::where('Status', 'COMPLETED')->sum('Amount'),
                'avg_transaction'    => Transaction::where('Status', 'COMPLETED')->avg('Amount') ?? 0,
            ],
        ];

        $growthData = User::where('created_at', '>=', now()->subMonths(6))
            ->get()
            ->groupBy(fn($u) => $u->created_at->format('Y-m'))
            ->map(fn($group, $month) => ['month' => $month, 'count' => $group->count()])
            ->sortBy('month')
            ->values();

        $recentTransactions = Transaction::with('user')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return Inertia::render('Admin/Statistics/Index', [
            'stats'              => $stats,
            'growthData'         => $growthData,
            'recentTransactions' => $recentTransactions,
        ]);
    }
}
