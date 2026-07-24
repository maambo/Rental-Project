<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\WorkerPortfolioPhoto;
use App\Services\WorkerProfileService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PortfolioController extends Controller
{
    public function __construct(private WorkerProfileService $service) {}

    public function index(Request $request)
    {
        $profile = $request->user()->workerProfile ?? abort(404);

        return Inertia::render('Worker/Portfolio/Index', [
            'profile' => $profile,
            'photos'  => $profile->portfolioPhotos()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'photo'   => ['required', 'image', 'max:5120'],
            'caption' => ['nullable', 'string', 'max:120'],
        ]);

        $profile = $request->user()->workerProfile ?? abort(404);
        abort_if($profile->portfolioPhotos()->count() >= 20, 422, 'Maximum 20 portfolio photos allowed.');

        $this->service->addPortfolioPhoto($profile, $request->file('photo'), $request->caption);

        return back()->with('success', 'Photo added to portfolio.');
    }

    public function destroy(Request $request, WorkerPortfolioPhoto $photo)
    {
        $profile = $request->user()->workerProfile;
        abort_if(!$profile || $photo->worker_profile_id !== $profile->id, 403);

        $this->service->deletePortfolioPhoto($photo);

        return back()->with('success', 'Photo removed.');
    }
}
