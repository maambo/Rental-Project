<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Province;
use App\Models\UtilityType;
use App\Services\PropertyAnalyticsService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PropertyAnalyticsController extends Controller
{
    public function __construct(private readonly PropertyAnalyticsService $analytics) {}

    public function index(Request $request)
    {
        $filters = $this->filters($request);

        return Inertia::render('Admin/Analytics/Properties', [
            'filters'      => $filters,
            'summary'      => $this->analytics->summary($filters),
            'byProvince'   => $this->analytics->byProvince($filters),
            'byDistrict'   => $this->analytics->byDistrict($filters),
            'byType'       => $this->analytics->byPropertyType($filters),
            'byListing'    => $this->analytics->byListingType($filters),
            'byUtility'    => $this->analytics->byUtility($filters),
            'provinces'    => Province::ordered()->get(['id', 'name']),
            'utilityTypes' => UtilityType::active()->get(['id', 'name']),
            'propertyTypes'    => ['residential', 'commercial'],
            'propertySubtypes' => ['house', 'apartment', 'room', 'farm', 'plot', 'shop', 'office_space', 'warehouse'],
        ]);
    }

    public function exportCsv(Request $request)
    {
        $filters = $this->filters($request);
        $rows = $this->analytics->exportRows($filters);

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'Code', 'Title', 'Type', 'Subtype', 'Listing', 'Province', 'District', 'Town',
                'Price (K)', 'Bedrooms', 'Bathrooms', 'Utilities', 'Approval Status', 'Availability', 'Listed On',
            ]);
            foreach ($rows as $p) {
                fputcsv($out, [
                    $p->code,
                    $p->title,
                    $p->property_type,
                    $p->property_subtype,
                    $p->listing_type,
                    $p->province?->name,
                    $p->district?->name,
                    $p->town?->name,
                    $p->price,
                    $p->bedrooms,
                    $p->bathrooms,
                    $p->utilities->pluck('name')->implode(', '),
                    $p->approval_status,
                    $p->availability_status,
                    $p->created_at?->toDateString(),
                ]);
            }
            fclose($out);
        }, 'property-analytics-' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportPdf(Request $request)
    {
        $filters = $this->filters($request);

        $pdf = app('dompdf.wrapper')->loadView('reports.property-analytics-pdf', [
            'filters'    => $filters,
            'summary'    => $this->analytics->summary($filters),
            'byProvince' => $this->analytics->byProvince($filters),
            'byDistrict' => $this->analytics->byDistrict($filters),
            'byType'     => $this->analytics->byPropertyType($filters),
            'byListing'  => $this->analytics->byListingType($filters),
            'byUtility'  => $this->analytics->byUtility($filters),
        ]);

        return $pdf->download('property-analytics-' . now()->format('Y-m-d') . '.pdf');
    }

    private function filters(Request $request): array
    {
        return $request->only([
            'province_id', 'district_id', 'town_id',
            'property_type', 'property_subtype', 'listing_type',
            'availability_status', 'approval_status', 'utility_type_id',
        ]);
    }
}
