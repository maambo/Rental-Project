<?php

namespace Tests\Feature\Api;

use App\Models\LandlordRating;
use App\Models\PropertyReport;
use App\Models\PropertyReview;
use App\Models\ViewingConfirmation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tests\Traits\RentalTestSupport;

class PropertyInteractionsTest extends TestCase
{
    use RefreshDatabase, RentalTestSupport;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();

        // Register unrouted controllers for testing only
        Route::middleware('auth:sanctum')->post('/test/viewing-confirmations', \App\Http\Controllers\Api\ViewingConfirmationController::class . '@store');
        Route::middleware('auth:sanctum')->post('/test/landlord-ratings', \App\Http\Controllers\Api\LandlordRatingController::class . '@store');
    }

    // ── PropertyReview ────────────────────────────────────────────────

    public function test_tenant_can_submit_property_review(): void
    {
        $landlord = $this->makeLandlord();
        $tenant   = $this->makeTenant();
        $property = $this->makeProperty($landlord);

        Sanctum::actingAs($tenant);

        $response = $this->postJson("/api/properties/{$property->id}/reviews", [
            'rating'  => 4,
            'comment' => 'Great place to live.',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('property_reviews', [
            'property_id' => $property->id,
            'user_id'     => $tenant->id,
            'rating'      => 4,
        ]);
    }

    public function test_submitting_second_review_updates_existing(): void
    {
        $landlord = $this->makeLandlord();
        $tenant   = $this->makeTenant();
        $property = $this->makeProperty($landlord);

        PropertyReview::create(['property_id' => $property->id, 'user_id' => $tenant->id, 'rating' => 3, 'comment' => 'Okay.']);

        Sanctum::actingAs($tenant);

        $response = $this->postJson("/api/properties/{$property->id}/reviews", [
            'rating'  => 5,
            'comment' => 'Changed my mind — excellent!',
        ]);

        $response->assertOk();
        $this->assertEquals(1, PropertyReview::where('property_id', $property->id)->count());
        $this->assertDatabaseHas('property_reviews', ['rating' => 5]);
    }

    public function test_review_requires_rating_and_comment(): void
    {
        $landlord = $this->makeLandlord();
        $property = $this->makeProperty($landlord);
        Sanctum::actingAs($this->makeTenant());

        $this->postJson("/api/properties/{$property->id}/reviews", [])
             ->assertUnprocessable();
    }

    // ── SavedProperty (wishlist) ──────────────────────────────────────

    public function test_tenant_can_list_wishlist(): void
    {
        $tenant = $this->makeTenant();
        Sanctum::actingAs($tenant);

        $this->getJson('/api/wishlist')->assertOk();
    }

    public function test_tenant_can_save_a_property(): void
    {
        $landlord = $this->makeLandlord();
        $tenant   = $this->makeTenant();
        $property = $this->makeProperty($landlord);

        Sanctum::actingAs($tenant);

        $this->postJson('/api/wishlist', ['property_id' => $property->id])
             ->assertStatus(201);

        $this->assertDatabaseHas('saved_properties', [
            'user_id'     => $tenant->id,
            'property_id' => $property->id,
        ]);
    }

    public function test_saving_same_property_twice_is_idempotent(): void
    {
        $landlord = $this->makeLandlord();
        $tenant   = $this->makeTenant();
        $property = $this->makeProperty($landlord);

        Sanctum::actingAs($tenant);

        $this->postJson('/api/wishlist', ['property_id' => $property->id]);
        $this->postJson('/api/wishlist', ['property_id' => $property->id]);

        $this->assertEquals(1, $tenant->savedProperties()->count());
    }

    public function test_tenant_can_remove_saved_property(): void
    {
        $landlord = $this->makeLandlord();
        $tenant   = $this->makeTenant();
        $property = $this->makeProperty($landlord);

        $tenant->savedProperties()->create(['property_id' => $property->id]);

        Sanctum::actingAs($tenant);

        $this->deleteJson("/api/wishlist/{$property->id}")
             ->assertOk()
             ->assertJsonFragment(['message' => 'Property removed from wishlist']);

        $this->assertDatabaseMissing('saved_properties', ['property_id' => $property->id]);
    }

    // ── ViewingConfirmation ───────────────────────────────────────────

    public function test_tenant_can_confirm_viewing(): void
    {
        $landlord = $this->makeLandlord();
        $tenant   = $this->makeTenant();
        $property = $this->makeProperty($landlord);

        Sanctum::actingAs($tenant);

        $response = $this->postJson('/test/viewing-confirmations', ['property_id' => $property->id]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['message' => 'Viewing confirmed']);

        $this->assertDatabaseHas('viewing_confirmations', [
            'property_id' => $property->id,
            'tenant_id'   => $tenant->id,
        ]);
    }

    public function test_confirming_viewing_twice_returns_409(): void
    {
        $landlord = $this->makeLandlord();
        $tenant   = $this->makeTenant();
        $property = $this->makeProperty($landlord);

        ViewingConfirmation::create([
            'property_id' => $property->id,
            'tenant_id'   => $tenant->id,
            'landlord_id' => $landlord->id,
            'confirmed_at' => now(),
        ]);

        Sanctum::actingAs($tenant);

        $this->postJson('/test/viewing-confirmations', ['property_id' => $property->id])
             ->assertStatus(409);
    }

    // ── LandlordRating ────────────────────────────────────────────────

    public function test_tenant_can_rate_landlord(): void
    {
        $landlord = $this->makeLandlord();
        $tenant   = $this->makeTenant();

        Sanctum::actingAs($tenant);

        $response = $this->postJson('/test/landlord-ratings', [
            'landlord_id' => $landlord->id,
            'rating'      => 4,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('landlord_ratings', [
            'landlord_id' => $landlord->id,
            'tenant_id'   => $tenant->id,
            'rating'      => 4,
        ]);
    }

    public function test_rating_is_verified_when_viewing_was_confirmed(): void
    {
        $landlord = $this->makeLandlord();
        $tenant   = $this->makeTenant();
        $property = $this->makeProperty($landlord);

        ViewingConfirmation::create([
            'property_id'  => $property->id,
            'tenant_id'    => $tenant->id,
            'landlord_id'  => $landlord->id,
            'confirmed_at' => now(),
        ]);

        Sanctum::actingAs($tenant);

        $this->postJson('/test/landlord-ratings', [
            'landlord_id' => $landlord->id,
            'property_id' => $property->id,
            'rating'      => 5,
        ]);

        $this->assertDatabaseHas('landlord_ratings', [
            'is_verified_rental' => true,
        ]);
    }
}
