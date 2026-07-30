<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\ReportingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TenantReportController extends Controller
{
    public function __construct(private readonly ReportingService $reporting) {}

    public function index(Request $request)
    {
        $from = $request->filled('from') ? Carbon::parse($request->string('from')) : now()->startOfYear();
        $to = $request->filled('to') ? Carbon::parse($request->string('to')) : now();

        return Inertia::render('Reports/Tenant/Index', [
            'from' => $from->toDateString(),
            'to' => $to->endOfDay()->toDateString(),
            'history' => $this->reporting->tenantPaymentHistory($request->user(), $from, $to->endOfDay()),
        ]);
    }
}
