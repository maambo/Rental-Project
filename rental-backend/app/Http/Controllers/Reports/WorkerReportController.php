<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\ReportingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WorkerReportController extends Controller
{
    public function __construct(private readonly ReportingService $reporting) {}

    public function index(Request $request)
    {
        $worker = $request->user();
        $from = $request->filled('from') ? Carbon::parse($request->string('from')) : now()->startOfYear();
        $to = ($request->filled('to') ? Carbon::parse($request->string('to')) : now())->endOfDay();

        $income = $this->reporting->workerIncomeBreakdown($worker, $from, $to);

        // Drill-through: clicking a service reveals the individual completed bookings.
        $drillService = $request->string('service')->toString() ?: null;
        $serviceDetail = $drillService
            ? $this->reporting->workerServiceBookingDetail($worker, $drillService, $from, $to)
            : null;

        return Inertia::render('Reports/Worker/Index', [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'income' => $income,
            'drillService' => $drillService,
            'serviceDetail' => $serviceDetail,
        ]);
    }
}
