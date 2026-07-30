<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PropertyReport;
use Inertia\Inertia;

class PropertyReportController extends Controller
{
    /**
     * Moderation queue for reported properties.
     *
     * Renders the Inertia page. The API-side counterpart
     * (Api\PropertyReportController) only handles report submission.
     */
    public function index()
    {
        return Inertia::render('Admin/Reports/Index', [
            'reports' => PropertyReport::with(['property', 'reporter'])
                ->orderByDesc('created_at')
                ->paginate(20),
        ]);
    }
}
