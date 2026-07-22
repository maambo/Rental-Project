<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\PropertyApplication;
use Inertia\Inertia;

class ApplicationController extends Controller
{
    public function index()
    {
        $applications = PropertyApplication::where('user_id', auth()->id())
            ->with(['property.province', 'property.district', 'property.images'])
            ->latest()
            ->get();

        return Inertia::render('Tenant/Applications/Index', [
            'applications' => $applications,
        ]);
    }
}
