<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Tests\Traits\RentalTestSupport;

class LocationApiTest extends TestCase
{
    use RefreshDatabase, RentalTestSupport;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
    }

    public function test_provinces_returns_list(): void
    {
        $this->makeLocation();

        $response = $this->getJson('/api/locations/provinces');

        $response->assertOk()
                 ->assertJsonStructure([['id', 'name', 'code']]);
    }

    public function test_districts_filtered_by_province(): void
    {
        [$province, $district] = $this->makeLocation();

        $response = $this->getJson("/api/locations/districts/{$province->id}");

        $response->assertOk()
                 ->assertJsonFragment(['province_id' => $province->id]);
    }

    public function test_towns_filtered_by_district(): void
    {
        [, $district, $town] = $this->makeLocation();

        $response = $this->getJson("/api/locations/towns/{$district->id}");

        $response->assertOk()
                 ->assertJsonFragment(['district_id' => $district->id]);
    }

    public function test_reverse_validates_lat_within_zambia_bounds(): void
    {
        $response = $this->getJson('/api/locations/reverse?lat=0&lng=28.0');

        $response->assertUnprocessable();
    }

    public function test_reverse_validates_lng_within_zambia_bounds(): void
    {
        $response = $this->getJson('/api/locations/reverse?lat=-15.0&lng=5.0');

        $response->assertUnprocessable();
    }

    public function test_reverse_calls_nominatim_and_returns_ids(): void
    {
        Http::fake([
            'nominatim.openstreetmap.org/*' => Http::response([
                'address' => [
                    'state'  => 'Lusaka Province',
                    'county' => 'Lusaka District',
                    'city'   => 'Lusaka',
                    'road'   => 'Independence Avenue',
                ],
            ], 200),
        ]);

        $response = $this->getJson('/api/locations/reverse?lat=-15.4&lng=28.3');

        $response->assertOk()
                 ->assertJsonStructure(['province_id', 'district_id', 'town_id', 'street_address']);
    }

    public function test_reverse_returns_nulls_when_nominatim_fails(): void
    {
        Http::fake([
            'nominatim.openstreetmap.org/*' => Http::response(null, 503),
        ]);

        $response = $this->getJson('/api/locations/reverse?lat=-15.4&lng=28.3');

        $response->assertOk()
                 ->assertJson(['province_id' => null, 'district_id' => null]);
    }

    public function test_search_requires_minimum_two_characters(): void
    {
        $response = $this->getJson('/api/locations/search?q=L');

        $response->assertUnprocessable();
    }

    public function test_search_calls_nominatim_and_returns_places(): void
    {
        Http::fake([
            'nominatim.openstreetmap.org/*' => Http::response([
                [
                    'place_id'     => 12345,
                    'display_name' => 'Lusaka, Zambia',
                    'lat'          => '-15.4',
                    'lon'          => '28.3',
                ],
            ], 200),
        ]);

        $response = $this->getJson('/api/locations/search?q=Lusaka');

        $response->assertOk()
                 ->assertJsonFragment(['display_name' => 'Lusaka, Zambia']);
    }

    public function test_search_filters_out_of_bounds_places(): void
    {
        Http::fake([
            'nominatim.openstreetmap.org/*' => Http::response([
                [
                    'place_id'     => 99,
                    'display_name' => 'Outside Zambia',
                    'lat'          => '0.0',  // outside Zambia bounding box
                    'lon'          => '28.3',
                ],
            ], 200),
        ]);

        $response = $this->getJson('/api/locations/search?q=Outside');

        $response->assertOk()->assertJson([]);
    }
}
