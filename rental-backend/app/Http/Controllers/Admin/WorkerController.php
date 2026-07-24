<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TradeCategory;
use App\Models\WorkerProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class WorkerController extends Controller
{
    public function index(Request $request)
    {
        $workers = WorkerProfile::with(['user', 'category', 'town'])
            ->when($request->search, fn ($q, $s) => $q->search($s))
            ->when($request->category, fn ($q, $c) => $q->inCategory((int) $c))
            ->when($request->status === 'pending', fn ($q) => $q->where('is_verified', false))
            ->when($request->status === 'verified', fn ($q) => $q->verified())
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('Admin/Workers/Index', [
            'workers'    => $workers,
            'categories' => TradeCategory::active()->get(['id', 'name']),
            'filters'    => $request->only(['search', 'category', 'status']),
        ]);
    }

    public function verify(Request $request, WorkerProfile $workerProfile)
    {
        $workerProfile->update([
            'is_verified' => true,
            'verified_at' => now(),
            'verified_by' => $request->user()->id,
        ]);

        return back()->with('success', "Worker verified: {$workerProfile->user->name}");
    }

    public function revoke(Request $request, WorkerProfile $workerProfile)
    {
        $workerProfile->update([
            'is_verified' => false,
            'verified_at' => null,
            'verified_by' => null,
        ]);

        return back()->with('success', "Verification revoked for {$workerProfile->user->name}");
    }

    public function toggleFeatured(WorkerProfile $workerProfile)
    {
        $workerProfile->update(['is_featured' => !$workerProfile->is_featured]);

        $status = $workerProfile->is_featured ? 'featured' : 'unfeatured';
        return back()->with('success', "Worker {$status}.");
    }

    // ── Trade Categories ─────────────────────────────────────────────

    public function categories()
    {
        return Inertia::render('Admin/Workers/Categories', [
            'categories' => TradeCategory::withCount('workerProfiles')->orderBy('sort_order')->get(),
        ]);
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:80', 'unique:trade_categories'],
            'icon'       => ['nullable', 'string', 'max:10'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        TradeCategory::create(array_merge($data, ['slug' => Str::slug($data['name'])]));

        return back()->with('success', 'Category added.');
    }

    public function toggleCategory(TradeCategory $tradeCategory)
    {
        $tradeCategory->update(['is_active' => !$tradeCategory->is_active]);
        return back()->with('success', 'Category updated.');
    }
}
