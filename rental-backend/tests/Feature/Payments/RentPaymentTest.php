<?php

namespace Tests\Feature\Payments;

use App\Models\PropertyApplication;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use Tests\Traits\RentalTestSupport;

class RentPaymentTest extends TestCase
{
    use RefreshDatabase, RentalTestSupport;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
        Mail::fake();
    }

    private function paymentRequestedApplication()
    {
        $landlord = $this->makeLandlord();
        $property = $this->makeProperty($landlord);
        $tenant   = $this->makeTenant();

        $application = PropertyApplication::create([
            'property_id'          => $property->id,
            'user_id'              => $tenant->id,
            'status'               => 'payment_requested',
            'payment_requested_at' => now(),
            'payment_deadline'     => now()->addHours(48),
        ]);

        return [$tenant, $property, $application];
    }

    public function test_tenant_can_pay_rent_via_simulated_mobile_money(): void
    {
        [$tenant, $property, $application] = $this->paymentRequestedApplication();

        $this->actingAs($tenant)
            ->post(route('properties.pay', [$property, $application]), [
                'method'   => 'mobile_money',
                'provider' => 'airtel',
                'phone'    => '0977123456',
            ])
            ->assertRedirect(route('tenant.applications.index'));

        $this->assertDatabaseHas('property_applications', [
            'id'     => $application->id,
            'status' => 'completed',
        ]);

        $transaction = Transaction::where('user_id', $tenant->id)->first();
        $this->assertNotNull($transaction);
        $this->assertSame('MOBILE_MONEY', $transaction->Type);
        $this->assertSame('COMPLETED', $transaction->Status);

        $data = json_decode($transaction->Data, true);
        $this->assertSame('airtel', $data['provider']);
        $this->assertSame('3456', $data['phone_last4']);
        // The full phone number must never be persisted.
        $this->assertStringNotContainsString('0977123456', $transaction->Data);
    }

    public function test_tenant_can_pay_rent_via_simulated_card(): void
    {
        [$tenant, $property, $application] = $this->paymentRequestedApplication();

        $this->actingAs($tenant)
            ->post(route('properties.pay', [$property, $application]), [
                'method'          => 'card',
                'card_number'     => '4242424242424242',
                'card_expiry'     => '09/28',
                'card_cvv'        => '123',
                'cardholder_name' => 'Jane Tenant',
            ])
            ->assertRedirect(route('tenant.applications.index'));

        $transaction = Transaction::where('user_id', $tenant->id)->first();
        $this->assertSame('CARD', $transaction->Type);

        $data = json_decode($transaction->Data, true);
        $this->assertSame('visa', $data['brand']);
        $this->assertSame('4242', $data['last4']);
        // Full card number/CVV must never be persisted.
        $this->assertStringNotContainsString('4242424242424242', $transaction->Data);
        $this->assertStringNotContainsString('123', $transaction->Data);
    }

    public function test_rent_payment_requires_a_valid_method_payload(): void
    {
        [$tenant, $property, $application] = $this->paymentRequestedApplication();

        $this->actingAs($tenant)
            ->post(route('properties.pay', [$property, $application]), [
                'method' => 'mobile_money',
                // missing provider/phone
            ])
            ->assertSessionHasErrors();

        $this->assertDatabaseHas('property_applications', [
            'id'     => $application->id,
            'status' => 'payment_requested',
        ]);

        $this->assertDatabaseCount('transactions', 0);
    }
}
