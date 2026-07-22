<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\PropertyApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PropertyController extends Controller
{
    public function show($id)
    {
        $user = Auth::user();
        $isOwner = false;

        // Base relationships loaded for everyone
        $with = ['images', 'landlord', 'province', 'district', 'town', 'utilities.options'];

        // Identify owner before loading optional relationships
        $property = Property::findOrFail($id);
        $isOwner = $user && $user->id === $property->landlord_id;

        // Owner gets reviews too (for their own engagement info), tenants get reviews for display
        $with[] = 'reviews.user';

        // Owner-only: documents and applications breakdown
        if ($isOwner) {
            $with[] = 'documents';
            $with[] = 'applications';
        }

        $property->load($with);
        $property->loadCount('reviews');

        // Only increment view count for non-owners
        if (!$isOwner) {
            $property->increment('view_count');
        }

        $property->average_rating = $property->reviews()->avg('rating') ?? 0;

        $applicantCount = PropertyApplication::where('property_id', $property->id)
            ->whereNotIn('status', ['cancelled'])
            ->count();

        // Strip sensitive fields from non-owners at the data level
        if (!$isOwner) {
            $property->makeHidden(['rejection_reason', 'view_count', 'submitted_date', 'approved_date', 'availability_changed_at', 'code', 'is_visible_in_search']);
        }

        return Inertia::render('Properties/Show', [
            'property'       => $property,
            'applicantCount' => $applicantCount,
            'isOwner'        => $isOwner,
        ]);
    }
}
