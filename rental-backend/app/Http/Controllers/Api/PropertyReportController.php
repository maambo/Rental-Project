<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyReport;
use Illuminate\Http\Request;

class PropertyReportController extends Controller
{
    // Number of reports before a property is auto-suspended.
    // Will move to a system_settings table in the PostgreSQL migration phase.
    private const AUTO_SUSPEND_THRESHOLD = 5;

    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'reasons'     => 'required|array|min:1',
            'reasons.*'   => 'string',
            'details'     => 'nullable|string|max:1000',
        ]);

        PropertyReport::create([
            'property_id'        => $validated['property_id'],
            'reported_by'        => auth()->id(),
            'report_reasons'     => $validated['reasons'],
            'additional_details' => $validated['details'] ?? null,
            'status'             => 'pending',
        ]);

        $property = Property::findOrFail($validated['property_id']);
        $property->increment('report_count');

        if ($property->report_count >= self::AUTO_SUSPEND_THRESHOLD) {
            $property->update([
                'is_auto_suspended'    => true,
                'is_visible_in_search' => false,
            ]);
        }

        return response()->json(['message' => 'Report submitted successfully'], 201);
    }

    public function index()
    {
        return PropertyReport::with(['property', 'reporter'])
            ->orderByDesc('created_at')
            ->paginate(20);
    }
}
