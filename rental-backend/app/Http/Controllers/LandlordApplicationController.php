<?php

namespace App\Http\Controllers;

use App\Http\Requests\LandlordApplication\StoreLandlordApplicationRequest;
use App\Http\Requests\LandlordApplication\UpdateLandlordApplicationRequest;
use App\Models\LandlordApplication;
use App\Services\LandlordApplicationService;
use Inertia\Inertia;

class LandlordApplicationController extends Controller
{
    public function __construct(private LandlordApplicationService $service) {}

    public function create()
    {
        return Inertia::render('Landlord/Apply');
    }

    public function store(StoreLandlordApplicationRequest $request)
    {
        $documents   = $this->service->storeDocuments($request);
        $application = $this->service->createUserAndApplication($request->validated(), $documents);

        auth()->login($application->user);

        return redirect()->route('landlord.status')
            ->with('success', 'Application submitted. Your account has been created and is pending review.');
    }

    public function status()
    {
        $application = LandlordApplication::forUser(auth()->id())->first();

        if (!$application) {
            return redirect()->route('landlord.apply')
                ->with('info', 'You have not submitted a landlord application yet.');
        }

        return Inertia::render('Landlord/ApplicationStatus', [
            'application' => $application,
        ]);
    }

    public function edit()
    {
        $application = LandlordApplication::forUser(auth()->id())->first();

        if (!$application) {
            return redirect()->route('landlord.apply')
                ->with('info', 'You have not submitted a landlord application yet.');
        }

        return Inertia::render('Landlord/EditApplication', [
            'application' => $application,
            'user'        => auth()->user(),
        ]);
    }

    public function update(UpdateLandlordApplicationRequest $request)
    {
        $application = LandlordApplication::forUser(auth()->id())->firstOrFail();

        if (!$application->isEditable()) {
            return back()->with('error', 'You cannot edit an application that has been approved or is under review.');
        }

        $documents = $this->service->replaceDocuments($request, $application);

        $application->update(array_merge(
            $request->only(['address', 'province', 'town', 'landlord_type']),
            $documents
        ));

        return redirect()->route('landlord.status')
            ->with('success', 'Application updated successfully.');
    }
}
