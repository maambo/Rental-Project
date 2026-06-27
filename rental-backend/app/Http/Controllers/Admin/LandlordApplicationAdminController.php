<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandlordApplication;
use App\Services\LandlordApplicationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LandlordApplicationAdminController extends Controller
{
    public function __construct(private LandlordApplicationService $service) {}

    public function index(Request $request)
    {
        $status = $request->get('status', 'all');

        $query = LandlordApplication::with('user')
            ->orderByDesc('created_at');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $applications = $query->get();

        $stats = [
            'total'        => LandlordApplication::count(),
            'pending'      => LandlordApplication::where('status', 'pending')->count(),
            'under_review' => LandlordApplication::where('status', 'under_review')->count(),
            'approved'     => LandlordApplication::where('status', 'approved')->count(),
            'rejected'     => LandlordApplication::where('status', 'rejected')->count(),
        ];

        return Inertia::render('Admin/Applications/Index', [
            'applications'  => $applications,
            'stats'         => $stats,
            'currentStatus' => $status,
        ]);
    }

    public function show($id)
    {
        $application = LandlordApplication::with('user')
            ->findOrFail($id);

        return Inertia::render('Admin/Applications/Show', [
            'application' => $application,
        ]);
    }

    public function markAsUnderReview($id)
    {
        LandlordApplication::findOrFail($id)->update(['status' => 'under_review']);

        return back()->with('success', 'Application marked as under review.');
    }

    public function approve($id)
    {
        $application = LandlordApplication::with('user')->findOrFail($id);

        $application->update([
            'status'      => 'approved',
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        $this->service->promoteToLandlord($application->user);

        return redirect()->route('admin.applications.index')
            ->with('success', 'Application approved. User is now a landlord.');
    }

    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        LandlordApplication::findOrFail($id)->update([
            'status'           => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'reviewed_at'      => now(),
            'reviewed_by'      => auth()->id(),
        ]);

        return redirect()->route('admin.applications.index')
            ->with('success', 'Application rejected.');
    }
}
