<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Http\Requests\Worker\StoreWorkerProfileRequest;
use App\Http\Requests\Worker\UpdateWorkerProfileRequest;
use App\Models\TradeCategory;
use App\Models\Town;
use App\Services\WorkerProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function __construct(private WorkerProfileService $service) {}

    public function create(Request $request)
    {
        if ($request->user()->workerProfile) {
            return redirect()->route('worker.dashboard');
        }

        return Inertia::render('Worker/Profile/Create', [
            'categories' => TradeCategory::active()->get(['id', 'name', 'icon']),
            'towns'      => Town::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreWorkerProfileRequest $request)
    {
        if ($request->user()->workerProfile) {
            return redirect()->route('worker.dashboard');
        }

        $data    = $request->validated();
        $profile = $request->user()->workerProfile()->create(Arr::except($data, ['profile_photo', 'certificate']));

        if ($request->hasFile('profile_photo')) {
            $this->service->storeProfilePhoto($profile, $request->file('profile_photo'));
        }

        if ($request->hasFile('certificate')) {
            $this->service->storeCertificate($profile, $request->file('certificate'));
        }

        return redirect()->route('worker.dashboard')->with('success', 'Worker profile created! You can now add services and portfolio photos.');
    }

    public function edit(Request $request)
    {
        $profile = $request->user()->workerProfile ?? abort(404);

        return Inertia::render('Worker/Profile/Edit', [
            'profile'    => $profile->load('category', 'town'),
            'categories' => TradeCategory::active()->get(['id', 'name', 'icon']),
            'towns'      => Town::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateWorkerProfileRequest $request)
    {
        $profile = $request->user()->workerProfile ?? abort(404);
        $data    = $request->validated();

        $profile->update(Arr::except($data, ['profile_photo', 'certificate']));

        if ($request->hasFile('profile_photo')) {
            $this->service->storeProfilePhoto($profile, $request->file('profile_photo'));
        }

        if ($request->hasFile('certificate')) {
            $this->service->storeCertificate($profile, $request->file('certificate'));
        }

        return back()->with('success', 'Profile updated.');
    }
}
