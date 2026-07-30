<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\ReportingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminFinancialReportController extends Controller
{
    public function __construct(private readonly ReportingService $reporting) {}

    public function index(Request $request)
    {
        [$from, $to] = $this->resolveRange($request);

        $revenueByPeriod = $this->reporting->revenueByPeriod($from, $to);
        $subscriptionRevenue = $this->reporting->subscriptionRevenue($from, $to);
        $outstandingBalances = $this->reporting->outstandingBalances();

        // Drill-through: clicking a period in the chart/table reveals the individual
        // completed transactions that make it up, without leaving the page.
        $drillPeriod = $request->string('period')->toString() ?: null;
        $periodDetail = $drillPeriod ? $this->reporting->transactionsInPeriod($drillPeriod) : null;

        return Inertia::render('Reports/Admin/Index', [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'revenueByPeriod' => $revenueByPeriod,
            'subscriptionRevenue' => $subscriptionRevenue,
            'outstandingBalances' => $outstandingBalances,
            'totalRevenue' => $this->reporting->totalRevenue(),
            'averageTransaction' => $this->reporting->averageTransaction(),
            'drillPeriod' => $drillPeriod,
            'periodDetail' => $periodDetail,
        ]);
    }

    public function exportCsv(Request $request)
    {
        [$from, $to] = $this->resolveRange($request);
        $rows = $this->reporting->revenueByPeriod($from, $to);

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Period', 'Total Revenue (K)', 'Transaction Count']);
            foreach ($rows as $row) {
                fputcsv($out, [$row['period'], $row['total'], $row['count']]);
            }
            fclose($out);
        }, 'financial-report-' . $from->toDateString() . '-to-' . $to->toDateString() . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportPdf(Request $request)
    {
        [$from, $to] = $this->resolveRange($request);

        $pdf = app('dompdf.wrapper')->loadView('reports.financial-pdf', [
            'title' => 'Platform Financial Report',
            'from' => $from,
            'to' => $to,
            'rows' => $this->reporting->revenueByPeriod($from, $to),
            'total' => $this->reporting->totalRevenue(),
        ]);

        return $pdf->download('financial-report-' . $from->toDateString() . '-to-' . $to->toDateString() . '.pdf');
    }

    private function resolveRange(Request $request): array
    {
        $from = $request->filled('from') ? Carbon::parse($request->string('from')) : now()->startOfYear();
        $to = $request->filled('to') ? Carbon::parse($request->string('to')) : now();

        return [$from->startOfDay(), $to->endOfDay()];
    }
}
