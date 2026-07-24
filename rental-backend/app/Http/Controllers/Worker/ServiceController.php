<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Http\Requests\Worker\StoreWorkerServiceRequest;
use App\Models\WorkerService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $profile = $request->user()->workerProfile ?? abort(404);

        return Inertia::render('Worker/Services/Index', [
            'profile'  => $profile,
            'services' => $profile->allServices()->get(),
        ]);
    }

    public function store(StoreWorkerServiceRequest $request)
    {
        $profile = $request->user()->workerProfile ?? abort(404);
        $profile->allServices()->create($request->validated());

        return back()->with('success', 'Service added.');
    }

    public function update(StoreWorkerServiceRequest $request, WorkerService $service)
    {
        $this->authorizeService($request, $service);
        $service->update($request->validated());

        return back()->with('success', 'Service updated.');
    }

    public function destroy(Request $request, WorkerService $service)
    {
        $this->authorizeService($request, $service);
        $service->delete();

        return back()->with('success', 'Service removed.');
    }

    private function authorizeService(Request $request, WorkerService $service): void
    {
        $profile = $request->user()->workerProfile;
        abort_if(!$profile || $service->worker_profile_id !== $profile->id, 403);
    }
}
