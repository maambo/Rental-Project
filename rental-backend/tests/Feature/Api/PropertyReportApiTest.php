<?php

namespace Tests\Feature\Api;

use App\Models\PropertyReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tests\Traits\RentalTestSupport;

class PropertyReportApiTest extends TestCase
{
    use RefreshDatabase, RentalTestSupport;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();

        // PropertyReportController::store has no public API route — register for tests only
        Route::middleware('auth:sanctum')->post('/test/property-reports', \App\Http\Controllers\Api\PropertyReportController::class . '@store');
    }

    public function test_authenticated_user_can_report_a_property(): void
    {
        $landlord = $this->makeLandlord();
        $tenant   = $this->makeTenant();
        $property = $this->makeProperty($landlord);

        Sanctum::actingAs($tenant);

        $response = $this->postJson('/test/property-reports', [
            'property_id' => $property->id,
            'reasons'     => ['scam', 'misleading_photos'],
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['message' => 'Report submitted successfully']);

        $this->assertDatabaseHas('property_reports', [
            'property_id' => $property->id,
            'reported_by' => $tenant->id,
        ]);
    }

    public function test_report_increments_report_count(): void
    {
        $landlord = $this->makeLandlord();
        $tenant   = $this->makeTenant();
        $property = $this->makeProperty($landlord);

        Sanctum::actingAs($tenant);

        $this->postJson('/test/property-reports', [
            'property_id' => $property->id,
            'reasons'     => ['scam'],
        ]);

        $this->assertEquals(1, $property->fresh()->report_count);
    }

    public function test_property_is_auto_suspended_after_five_reports(): void
    {
        $landlord = $this->makeLandlord();
        $property = $this->makeProperty($landlord);

        // Seed 4 reports directly
        $property->update(['report_count' => 4]);

        $tenant = $this->makeTenant();
        Sanctum::actingAs($tenant);

        $this->postJson('/test/property-reports', [
            'property_id' => $property->id,
            'reasons'     => ['scam'],
        ]);

        $fresh = $property->fresh();
        $this->assertTrue((bool) $fresh->is_auto_suspended);
        $this->assertFalse((bool) $fresh->is_visible_in_search);
    }

    public function test_report_requires_at_least_one_reason(): void
    {
        $landlord = $this->makeLandlord();
        $property = $this->makeProperty($landlord);

        Sanctum::actingAs($this->makeTenant());

        $this->postJson('/test/property-reports', [
            'property_id' => $property->id,
            'reasons'     => [],
        ])->assertUnprocessable();
    }
}
