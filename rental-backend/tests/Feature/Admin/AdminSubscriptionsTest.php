<?php

namespace Tests\Feature\Admin;

use App\Models\Subscription;
use App\Models\VerificationTier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RentalTestSupport;

class AdminSubscriptionsTest extends TestCase
{
    use RefreshDatabase, RentalTestSupport;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
    }

    private function makeTier(string $name = 'pro', string $tierType = 'landlord'): VerificationTier
    {
        return VerificationTier::create([
            'name'           => $name,
            'tier_type'      => $tierType,
            'display_name'   => ucfirst($name),
            'price_display'  => 'K200/mo',
            'price_amount'   => 200,
            'property_limit' => 10,
            'is_active'      => true,
        ]);
    }

    public function test_admin_can_view_subscriptions_index(): void
    {
        $this->actingAs($this->makeAdmin())
             ->get(route('admin.subscriptions.index'))
             ->assertOk()
             ->assertInertia(fn ($page) => $page->has('subscriptions'));
    }

    public function test_admin_can_create_subscription_for_user(): void
    {
        $admin    = $this->makeAdmin();
        $landlord = $this->makeLandlord();
        $tier     = $this->makeTier();

        $this->actingAs($admin)
             ->post(route('admin.subscriptions.store'), [
                 'user_id'              => $landlord->id,
                 'verification_tier_id' => $tier->id,
                 'billing_cycle'        => 'monthly',
             ])
             ->assertRedirect(route('admin.subscriptions.index'));

        $this->assertDatabaseHas('subscriptions', [
            'user_id'  => $landlord->id,
            'status'   => 'active',
        ]);
    }

    public function test_creating_subscription_cancels_existing_active_one(): void
    {
        $admin    = $this->makeAdmin();
        $landlord = $this->makeLandlord();
        $tier     = $this->makeTier();

        $existing = Subscription::create([
            'user_id'              => $landlord->id,
            'verification_tier_id' => $tier->id,
            'status'               => 'active',
            'billing_cycle'        => 'monthly',
            'starts_at'            => now(),
            'ends_at'              => now()->addMonth(),
        ]);

        $this->actingAs($admin)
             ->post(route('admin.subscriptions.store'), [
                 'user_id'              => $landlord->id,
                 'verification_tier_id' => $tier->id,
                 'billing_cycle'        => 'monthly',
             ]);

        $this->assertEquals('cancelled', $existing->fresh()->status);
        $this->assertEquals(1, Subscription::where('user_id', $landlord->id)->where('status', 'active')->count());
    }

    public function test_admin_can_cancel_subscription(): void
    {
        $admin    = $this->makeAdmin();
        $landlord = $this->makeLandlord();
        $tier     = $this->makeTier();

        $subscription = Subscription::create([
            'user_id'              => $landlord->id,
            'verification_tier_id' => $tier->id,
            'status'               => 'active',
            'billing_cycle'        => 'monthly',
            'starts_at'            => now(),
            'ends_at'              => now()->addMonth(),
        ]);

        $this->actingAs($admin)
             ->delete(route('admin.subscriptions.destroy', $subscription))
             ->assertRedirect();

        $this->assertEquals('cancelled', $subscription->fresh()->status);
    }

    public function test_non_admin_cannot_access_subscriptions(): void
    {
        $this->actingAs($this->makeTenant())
             ->get(route('admin.subscriptions.index'))
             ->assertForbidden();
    }
}
