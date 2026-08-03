<?php

namespace App\Services;

use App\Models\Property;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PropertyAnalyticsService
{
    /**
     * Base filtered query. Every breakdown method rebuilds from this rather than
     * sharing/cloning a builder instance, so filters can't leak between calls.
     */
    public function baseQuery(array $filters): Builder
    {
        // Columns qualified with the properties table — byDistrict()/byProvince() join
        // districts/provinces, both of which also carry a province_id column, so an
        // unqualified where('province_id', ...) is ambiguous once that join is added.
        return Property::query()
            ->when($filters['province_id'] ?? null, fn ($q, $v) => $q->where('properties.province_id', $v))
            ->when($filters['district_id'] ?? null, fn ($q, $v) => $q->where('properties.district_id', $v))
            ->when($filters['town_id'] ?? null, fn ($q, $v) => $q->where('properties.town_id', $v))
            ->when($filters['property_type'] ?? null, fn ($q, $v) => $q->where('properties.property_type', $v))
            ->when($filters['property_subtype'] ?? null, fn ($q, $v) => $q->where('properties.property_subtype', $v))
            ->when($filters['listing_type'] ?? null, fn ($q, $v) => $q->where('properties.listing_type', $v))
            ->when($filters['availability_status'] ?? null, fn ($q, $v) => $q->where('properties.availability_status', $v))
            ->when($filters['approval_status'] ?? null, fn ($q, $v) => $q->where('properties.approval_status', $v))
            ->when($filters['utility_type_id'] ?? null, function ($q, $v) {
                $q->whereHas('utilities', fn ($uq) => $uq->where('utility_types.id', $v));
            });
    }

    public function summary(array $filters): array
    {
        $query = $this->baseQuery($filters);

        return [
            'total_properties' => (clone $query)->count(),
            'avg_price'         => round((clone $query)->avg('price') ?? 0, 2),
            'total_views'       => (int) (clone $query)->sum('view_count'),
            'total_likes'       => (int) (clone $query)->sum('like_count'),
            'for_rent'          => (clone $query)->where('listing_type', 'rent')->count(),
            'for_sale'          => (clone $query)->where('listing_type', 'sale')->count(),
        ];
    }

    public function byProvince(array $filters): Collection
    {
        return $this->baseQuery($filters)
            ->join('provinces', 'provinces.id', '=', 'properties.province_id')
            ->selectRaw('provinces.id as province_id, provinces.name as label, COUNT(*) as total, AVG(properties.price) as avg_price')
            ->groupBy('provinces.id', 'provinces.name')
            ->orderByDesc('total')
            ->get();
    }

    public function byDistrict(array $filters): Collection
    {
        return $this->baseQuery($filters)
            ->join('districts', 'districts.id', '=', 'properties.district_id')
            ->selectRaw('districts.id as district_id, districts.name as label, COUNT(*) as total, AVG(properties.price) as avg_price')
            ->groupBy('districts.id', 'districts.name')
            ->orderByDesc('total')
            ->limit(50)
            ->get();
    }

    public function byPropertyType(array $filters): Collection
    {
        return $this->baseQuery($filters)
            ->selectRaw('property_type, property_subtype, COUNT(*) as total, AVG(price) as avg_price')
            ->groupBy('property_type', 'property_subtype')
            ->orderByDesc('total')
            ->get();
    }

    public function byListingType(array $filters): Collection
    {
        return $this->baseQuery($filters)
            ->selectRaw('listing_type, COUNT(*) as total, AVG(price) as avg_price')
            ->groupBy('listing_type')
            ->get();
    }

    /**
     * For each active utility type, how many of the filtered properties have it
     * configured. Counted independently of the other utility types (a property
     * can — and usually does — have several), so totals don't sum to the filtered
     * property count.
     */
    public function byUtility(array $filters): Collection
    {
        $propertyIds = $this->baseQuery($filters)->pluck('properties.id');

        if ($propertyIds->isEmpty()) {
            return collect();
        }

        return DB::table('property_utilities')
            ->join('utility_types', 'utility_types.id', '=', 'property_utilities.utility_type_id')
            ->whereIn('property_utilities.property_id', $propertyIds)
            ->selectRaw('utility_types.id, utility_types.name as label, COUNT(*) as total')
            ->groupBy('utility_types.id', 'utility_types.name')
            ->orderByDesc('total')
            ->get();
    }

    /**
     * Row-level export data: one row per property matching the filters, with its
     * utilities flattened into a single comma-separated column.
     */
    public function exportRows(array $filters): Collection
    {
        return $this->baseQuery($filters)
            ->with(['province', 'district', 'town', 'landlord', 'utilities'])
            ->orderByDesc('created_at')
            ->get();
    }
}
