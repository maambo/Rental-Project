<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PropertyReport;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PropertyReportController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'reasons' => 'required|array|min:1',
            'reasons.*' => 'string',
            'details' => 'nullable|string|max:1000',
        ]);

        $report = PropertyReport::create([
            'property_id' => $validated['property_id'],
            'reported_by' => auth()->id(),
            'report_reasons' => $validated['reasons'],
            'additional_details' => $validated['details'] ?? null,
            'status' => 'pending',
        ]);

        // Auto-moderation logic
        $property = Property::find($validated['property_id']);
        $property->increment('report_count');

        if ($property->report_count >= 5) {
            $property->update([
                'is_auto_suspended' => true,
                'is_visible_in_search' => false,
            ]);
            // TODO: Send notification to admin
        }

        return response()->json(['message' => 'Report submitted successfully', 'report' => $report], 201);
    }

    public function index()
    {
        // Admin only
        return PropertyReport::with(['property', 'reporter'])->orderBy('created_at', 'desc')->paginate(20);
    }
}
