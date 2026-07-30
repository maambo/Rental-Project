<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\ReportingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LandlordReportController extends Controller
{
    public function __construct(private readonly ReportingService $reporting) {}

    public function index(Request $request)
    {
        $landlord = $request->user();
        [$from, $to] = $this->resolveRange($request);

        $income = $this->reporting->landlordIncomeBreakdown($landlord, $from, $to);

        // Drill-through: clicking a property reveals its individual bills for the range.
        $drillPropertyId = $request->integer('property') ?: null;
        $propertyDetail = $drillPropertyId
            ? $this->reporting->landlordPropertyBillingDetail($landlord, $drillPropertyId, $from, $to)
            : null;

        return Inertia::render('Reports/Landlord/Index', [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'income' => $income,
            'drillPropertyId' => $drillPropertyId,
            'propertyDetail' => $propertyDetail,
        ]);
    }

    public function exportCsv(Request $request)
    {
        [$from, $to] = $this->resolveRange($request);
        $rows = $this->reporting->landlordIncomeBreakdown($request->user(), $from, $to);

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Property', 'Total Billed (K)', 'Total Paid (K)', 'Outstanding (K)', 'Bill Count']);
            foreach ($rows as $row) {
                fputcsv($out, [$row['property'], $row['total_billed'], $row['total_paid'], $row['outstanding'], $row['bill_count']]);
            }
            fclose($out);
        }, 'landlord-income-' . $from->toDateString() . '-to-' . $to->toDateString() . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportPdf(Request $request)
    {
        [$from, $to] = $this->resolveRange($request);
        $landlord = $request->user();

        $pdf = app('dompdf.wrapper')->loadView('reports.financial-pdf', [
            'title' => $landlord->name . ' — Income Report',
            'from' => $from,
            'to' => $to,
            'rows' => $this->reporting->landlordIncomeBreakdown($landlord, $from, $to)
                ->map(fn ($r) => ['period' => $r['property'], 'total' => $r['total_paid'], 'count' => $r['bill_count']]),
            'total' => $this->reporting->landlordIncomeBreakdown($landlord, $from, $to)->sum('total_paid'),
        ]);

        return $pdf->download('landlord-income-' . $from->toDateString() . '-to-' . $to->toDateString() . '.pdf');
    }

    private function resolveRange(Request $request): array
    {
        $from = $request->filled('from') ? Carbon::parse($request->string('from')) : now()->startOfYear();
        $to = $request->filled('to') ? Carbon::parse($request->string('to')) : now();

        return [$from->startOfDay(), $to->endOfDay()];
    }
}
