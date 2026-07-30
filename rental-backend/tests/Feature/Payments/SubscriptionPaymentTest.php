<?php

namespace Tests\Feature\Payments;

use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\VerificationTier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RentalTestSupport;

class SubscriptionPaymentTest extends TestCase
{
    use RefreshDatabase, RentalTestSupport;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
    }

    private function makeProfessionalTier(): VerificationTier
    {
        return VerificationTier::create([
            'name'            => 'professional',
            'tier_type'       => 'landlord',
            'display_name'    => 'Professional',
            'price_display'   => 'K299/mo',
            'price_amount'    => 299,
            'property_limit'  => 15,
            'is_active'       => true,
        ]);
    }

    public function test_landlord_sees_landlord_tiers_on_subscribe_index(): void
    {
        $landlord = $this->makeLandlord();
        $tier = $this->makeProfessionalTier();

        $response = $this->actingAs($landlord)->get(route('payments.subscribe.index'));

        // createRoles() already seeds a free "starter" landlord tier, so both it
        // and the professional tier created above should be visible here.
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Payments/Subscribe/Index')
            ->has('tiers', 2)
            ->where('tiers.1.id', $tier->id)
        );
    }

    public function test_landlord_can_subscribe_via_simulated_mobile_money(): void
    {
        $landlord = $this->makeLandlord();
        $tier = $this->makeProfessionalTier();

        $this->actingAs($landlord)
            ->post(route('payments.subscribe.store', $tier), [
                'billing_cycle' => 'monthly',
                'method'        => 'mobile_money',
                'provider'      => 'mtn',
                'phone'         => '0966123456',
            ])
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('subscriptions', [
            'user_id'              => $landlord->id,
            'verification_tier_id' => $tier->id,
            'status'               => 'active',
            'billing_cycle'        => 'monthly',
        ]);

        $transaction = Transaction::where('user_id', $landlord->id)->first();
        $this->assertNotNull($transaction);
        $this->assertEquals(299, $transaction->Amount);
    }

    public function test_subscribing_cancels_previous_active_subscription_of_same_tier_type(): void
    {
        $landlord = $this->makeLandlord();
        $starter = VerificationTier::where('name', 'starter')->first();
        $professional = $this->makeProfessionalTier();

        $oldSubscription = Subscription::create([
            'user_id'              => $landlord->id,
            'verification_tier_id' => $starter->id,
            'status'               => 'active',
            'billing_cycle'        => 'free',
            'starts_at'            => now()->subMonth(),
        ]);

        $this->actingAs($landlord)
            ->post(route('payments.subscribe.store', $professional), [
                'billing_cycle' => 'monthly',
                'method'        => 'card',
                'card_number'   => '5500000000000004',
                'card_expiry'   => '11/27',
                'card_cvv'      => '456',
                'cardholder_name' => 'Landlord Test',
            ]);

        $this->assertDatabaseHas('subscriptions', [
            'id'     => $oldSubscription->id,
            'status' => 'cancelled',
        ]);

        $this->assertDatabaseHas('subscriptions', [
            'user_id'              => $landlord->id,
            'verification_tier_id' => $professional->id,
            'status'               => 'active',
        ]);
    }

    public function test_subscription_checkout_requires_a_valid_billing_cycle(): void
    {
        $landlord = $this->makeLandlord();
        $tier = $this->makeProfessionalTier();

        $this->actingAs($landlord)
            ->post(route('payments.subscribe.store', $tier), [
                'method'   => 'mobile_money',
                'provider' => 'mtn',
                'phone'    => '0966123456',
                // missing billing_cycle
            ])
            ->assertSessionHasErrors('billing_cycle');
    }
}
