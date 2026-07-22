<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Province;
use App\Models\Town;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

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

    public function reverse(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric|between:-18.1,-8.2',
            'lng' => 'required|numeric|between:21.9,33.7',
        ]);

        $response = Http::withHeaders(['User-Agent' => 'RentalApp/1.0'])
            ->get('https://nominatim.openstreetmap.org/reverse', [
                'lat'            => $request->lat,
                'lon'            => $request->lng,
                'format'         => 'json',
                'addressdetails' => 1,
                'zoom'           => 18,
            ]);

        if (!$response->ok()) {
            return response()->json(['province_id' => null, 'district_id' => null, 'town_id' => null, 'street_address' => '']);
        }

        $addr = $response->json('address', []);

        // ── Match Province ───────────────────────────────────────────────
        $stateName = Str::before($addr['state'] ?? '', ' Province');
        $province  = Province::where('name', 'like', "%{$stateName}%")->first();

        // ── Match District ───────────────────────────────────────────────
        $district = null;
        if ($province) {
            $rawDistrict = $addr['county'] ?? $addr['district'] ?? $addr['state_district'] ?? '';
            $districtName = Str::before($rawDistrict, ' District');
            $district = District::where('province_id', $province->id)
                ->where('name', 'like', "%{$districtName}%")
                ->first();

            // Fallback: match by city/town name if district not found
            if (!$district && $districtName) {
                $district = District::where('province_id', $province->id)
                    ->orderByRaw('CHAR_LENGTH(name)')
                    ->first();
            }
        }

        // ── Match Town ───────────────────────────────────────────────────
        $town = null;
        if ($district) {
            $townName = $addr['city'] ?? $addr['town'] ?? $addr['village'] ?? $addr['suburb'] ?? '';
            $town = Town::where('district_id', $district->id)
                ->where('name', 'like', "%{$townName}%")
                ->first();

            // Fallback: try the suburb against all towns in district
            if (!$town && !empty($addr['suburb'])) {
                $town = Town::where('district_id', $district->id)
                    ->where('name', 'like', "%{$addr['suburb']}%")
                    ->first();
            }
        }

        // ── Build street address ─────────────────────────────────────────
        $streetParts = array_filter([
            isset($addr['house_number']) ? $addr['house_number'] : null,
            $addr['road'] ?? $addr['pedestrian'] ?? $addr['footway'] ?? null,
            $addr['suburb'] ?? $addr['neighbourhood'] ?? null,
        ]);
        $streetAddress = implode(', ', $streetParts);

        return response()->json([
            'province_id'    => $province?->id,
            'district_id'    => $district?->id,
            'town_id'        => $town?->id,
            'street_address' => $streetAddress,
        ]);
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
            return response()->json([]);
        }

        $results = collect($response->json())
            ->filter(function ($place) use ($bbox) {
                $lat = (float) $place['lat'];
                $lng = (float) $place['lon'];
                return $lat >= $bbox['min_lat'] && $lat <= $bbox['max_lat']
                    && $lng >= $bbox['min_lng'] && $lng <= $bbox['max_lng'];
            })
            ->map(fn($place) => [
                'place_id'     => $place['place_id'],
                'display_name' => $place['display_name'],
                'lat'          => $place['lat'],
                'lon'          => $place['lon'],
            ])
            ->values();

        return response()->json($results);
    }
}
