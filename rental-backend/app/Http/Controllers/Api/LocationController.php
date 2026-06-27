<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Province;
use App\Models\Town;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LocationController extends Controller
{
    // Zambia bounding box
    private const ZAMBIA_BBOX = [
        'min_lat' => -18.1,
        'max_lat' => -8.2,
        'min_lng' => 21.9,
        'max_lng' => 33.7,
    ];

    public function provinces()
    {
        return Province::ordered()->get(['id', 'name', 'code']);
    }

    public function districts(int $provinceId)
    {
        return District::where('province_id', $provinceId)
            ->ordered()
            ->get(['id', 'province_id', 'name']);
    }

    public function towns(int $districtId)
    {
        return Town::where('district_id', $districtId)
            ->ordered()
            ->get(['id', 'district_id', 'name', 'latitude', 'longitude']);
    }

    public function search(Request $request)
    {
        $request->validate(['q' => 'required|string|min:2|max:200']);

        $bbox = self::ZAMBIA_BBOX;

        $response = Http::withHeaders(['User-Agent' => 'RentalApp/1.0'])
            ->get('https://nominatim.openstreetmap.org/search', [
                'q'              => $request->q . ', Zambia',
                'format'         => 'json',
                'addressdetails' => 1,
                'limit'          => 8,
                'countrycodes'   => 'zm',
                'viewbox'        => "{$bbox['min_lng']},{$bbox['max_lat']},{$bbox['max_lng']},{$bbox['min_lat']}",
                'bounded'        => 1,
            ]);

        if (!$response->ok()) {
            return response()->json(['results' => []]);
        }

        $results = collect($response->json())
            ->filter(function ($place) use ($bbox) {
                $lat = (float) $place['lat'];
                $lng = (float) $place['lon'];
                return $lat >= $bbox['min_lat'] && $lat <= $bbox['max_lat']
                    && $lng >= $bbox['min_lng'] && $lng <= $bbox['max_lng'];
            })
            ->map(fn($place) => [
                'display_name' => $place['display_name'],
                'lat'          => (float) $place['lat'],
                'lng'          => (float) $place['lon'],
                'type'         => $place['type'] ?? null,
            ])
            ->values();

        return response()->json(['results' => $results]);
    }
}
