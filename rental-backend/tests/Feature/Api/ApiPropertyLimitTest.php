<?php

namespace Tests\Feature\Api;

use App\Models\Subscription;
use App\Models\VerificationTier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tests\Traits\RentalTestSupport;

class ApiPropertyLimitTest extends TestCase
{
    use RefreshDatabase, RentalTestSupport;

    private function validPayload(array $overrides = []): array
    {
        [$province, $district, $town] = $this->makeLocation();

        return array_merge([
            'province_id'      => $province->id,
            'district_id'      => $district->id,
            'town_id'          => $town->id,
            'street_address'   => '99 Api Street',
            'latitude'         => -15.4,
            'longitude'        => 28.3,
            'property_type'    => 'residential',
            'property_subtype' => 'apartment',
            'listing_type'     => 'rent',
            'title'            => 'API Test Property',
            'description'      => 'Property created via the API.',
            'price'            => 3000,
            'bedrooms'         => 2,
            'bathrooms'        => 1,
        ], $overrides);
    }

    public function test_landlord_within_limit_can_create_property_via_api(): void
    {
        $this->createRoles();
        $landlord = $this->makeLandlord();
        Sanctum::actingAs($landlord);

        // Starter tier (property_limit = 1) is seeded by createRoles().
        // No subscription → falls back to starter tier → limit = 1.
        $response = $this->postJson('/api/landlord/properties', $this->validPayload());

        $response->assertStatus(201);
    }

    public function test_landlord_at_limit_is_rejected_via_api(): void
    {
        $this->createRoles();
        $landlord = $this->makeLandlord();
        Sanctum::actingAs($landlord);

        // Create one property (reaches the starter limit of 1).
        $this->makeProperty($landlord);

        $response = $this->postJson('/api/landlord/properties', $this->validPayload());

        $response->assertStatus(403)
                 ->assertJsonFragment(['error' => 'Property limit reached for your current plan.']);
    }

    public function test_landlord_with_unlimited_tier_can_create_multiple_properties_via_api(): void
    {
        $this->createRoles();
        $landlord = $this->makeLandlord();
        Sanctum::actingAs($landlord);

        $unlimitedTier = VerificationTier::create([
            'name'           => 'unlimited',
            'tier_type'      => 'landlord',
            'display_name'   => 'Unlimited',
            'price_display'  => 'K1000/mo',
            'price_amount'   => 1000,
            'property_limit' => -1,
            'is_active'      => true,
        ]);

        Subscription::create([
            'user_id'              => $landlord->id,
            'verification_tier_id' => $unlimitedTier->id,
            'status'               => 'active',
            'billing_cycle'        => 'monthly',
            'starts_at'            => now(),
            'ends_at'              => now()->addYear(),
        ]);

        // Already has one property — should still succeed with unlimited tier.
        $this->makeProperty($landlord);

        $response = $this->postJson('/api/landlord/properties', $this->validPayload());

        $response->assertStatus(201);
    }
}
