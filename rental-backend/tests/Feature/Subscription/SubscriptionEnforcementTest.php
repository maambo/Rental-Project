<?php

namespace Tests\Feature\Subscription;

use App\Models\Property;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\User;
use App\Models\VerificationTier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionEnforcementTest extends TestCase
{
    use RefreshDatabase;

    private Role $landlordRole;
    private VerificationTier $starterTier;
    private VerificationTier $basicTier;
    private VerificationTier $professionalTier;
    private VerificationTier $enterpriseTier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->landlordRole = Role::create(['name' => 'landlord', 'display_name' => 'Landlord', 'description' => '']);

        $this->starterTier = VerificationTier::create([
            'name' => 'starter', 'tier_type' => 'landlord', 'display_name' => 'Starter',
            'price_display' => 'Free', 'price_amount' => 0, 'property_limit' => 1,
            'features' => [], 'styling' => [], 'is_active' => true,
        ]);

        $this->basicTier = VerificationTier::create([
            'name' => 'basic', 'tier_type' => 'landlord', 'display_name' => 'Basic',
            'price_display' => 'K99/mo', 'price_amount' => 99, 'property_limit' => 5,
            'features' => [], 'styling' => [], 'is_active' => true,
        ]);

        $this->professionalTier = VerificationTier::create([
            'name' => 'professional', 'tier_type' => 'landlord', 'display_name' => 'Professional',
            'price_display' => 'K299/mo', 'price_amount' => 299, 'property_limit' => 15,
            'features' => [], 'styling' => [], 'is_active' => true,
        ]);

        $this->enterpriseTier = VerificationTier::create([
            'name' => 'enterprise', 'tier_type' => 'landlord', 'display_name' => 'Enterprise',
            'price_display' => 'K699/mo', 'price_amount' => 699, 'property_limit' => -1,
            'features' => [], 'styling' => [], 'is_active' => true,
        ]);
    }

    private function landlordWithTier(VerificationTier $tier): User
    {
        $user = User::factory()->create(['role_id' => $this->landlordRole->id]);

        Subscription::create([
            'user_id'              => $user->id,
            'verification_tier_id' => $tier->id,
            'status'               => 'active',
            'billing_cycle'        => $tier->price_amount > 0 ? 'monthly' : 'free',
            'starts_at'            => now(),
        ]);

        return $user;
    }

    private function validPropertyData(User $user): array
    {
        return [
            'title'          => 'Test Property',
            'description'    => 'A test property description',
            'price'          => 1500,
            'property_type'  => 'residential',
            'property_subtype' => 'house',
            'listing_type'   => 'rent',
            'street_address' => '123 Test Road',
            'province_id'    => 1,
            'district_id'    => 1,
            'town_id'        => 1,
            'latitude'       => -15.4167,
            'longitude'      => 28.2833,
            'bedrooms'       => 3,
            'bathrooms'      => 2,
        ];
    }

    // ── Tier helpers on User ──────────────────────────────────────────────────

    public function test_user_property_limit_reflects_active_subscription_tier(): void
    {
        $starterLandlord = $this->landlordWithTier($this->starterTier);
        $basicLandlord   = $this->landlordWithTier($this->basicTier);

        $this->assertEquals(1, $starterLandlord->propertyLimit());
        $this->assertEquals(5, $basicLandlord->propertyLimit());
    }

    public function test_enterprise_tier_reports_unlimited_properties(): void
    {
        $landlord = $this->landlordWithTier($this->enterpriseTier);

        $this->assertTrue($landlord->hasUnlimitedProperties());
        $this->assertEquals(-1, $landlord->propertyLimit());
    }

    public function test_user_without_subscription_falls_back_to_starter_limit(): void
    {
        $user = User::factory()->create(['role_id' => $this->landlordRole->id]);

        $this->assertEquals(1, $user->propertyLimit());
        $this->assertFalse($user->hasUnlimitedProperties());
    }

    // ── Subscription model helpers ────────────────────────────────────────────

    public function test_active_subscription_is_detected_correctly(): void
    {
        $user = User::factory()->create(['role_id' => $this->landlordRole->id]);

        $sub = Subscription::create([
            'user_id'              => $user->id,
            'verification_tier_id' => $this->basicTier->id,
            'status'               => 'active',
            'billing_cycle'        => 'monthly',
            'starts_at'            => now(),
            'ends_at'              => now()->addMonth(),
        ]);

        $this->assertTrue($sub->isActive());
        $this->assertEquals(5, $sub->propertyLimit());
    }

    public function test_expired_subscription_is_detected(): void
    {
        $user = User::factory()->create(['role_id' => $this->landlordRole->id]);

        $sub = Subscription::create([
            'user_id'              => $user->id,
            'verification_tier_id' => $this->basicTier->id,
            'status'               => 'active',
            'billing_cycle'        => 'monthly',
            'starts_at'            => now()->subMonth(),
            'ends_at'              => now()->subDay(),
        ]);

        $this->assertFalse($sub->isActive());
    }

    // ── LandlordApplicationService — auto Starter subscription ───────────────

    public function test_promote_to_landlord_creates_starter_subscription(): void
    {
        $user = User::factory()->create(['role_id' => $this->landlordRole->id]);

        $service = app(\App\Services\LandlordApplicationService::class);
        $service->promoteToLandlord($user);

        $this->assertDatabaseHas('subscriptions', [
            'user_id'              => $user->id,
            'verification_tier_id' => $this->starterTier->id,
            'status'               => 'active',
            'billing_cycle'        => 'free',
        ]);
    }

    public function test_promote_to_landlord_does_not_duplicate_subscription(): void
    {
        $user = User::factory()->create(['role_id' => $this->landlordRole->id]);

        Subscription::create([
            'user_id'              => $user->id,
            'verification_tier_id' => $this->starterTier->id,
            'status'               => 'active',
            'billing_cycle'        => 'free',
            'starts_at'            => now(),
        ]);

        $service = app(\App\Services\LandlordApplicationService::class);
        $service->promoteToLandlord($user);

        $this->assertEquals(1, $user->subscriptions()->count());
    }
}
