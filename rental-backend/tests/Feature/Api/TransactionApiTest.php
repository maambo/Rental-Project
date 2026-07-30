<?php

namespace Tests\Feature\Api;

use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tests\Traits\RentalTestSupport;

class TransactionApiTest extends TestCase
{
    use RefreshDatabase, RentalTestSupport;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
    }

    public function test_authenticated_user_can_create_transaction(): void
    {
        $tenant = $this->makeTenant();
        Sanctum::actingAs($tenant);

        $response = $this->postJson('/api/transactions', [
            'amount' => 2500,
            'name'   => 'John Doe',
            'type'   => 'CARD',
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['message' => 'Payment processed successfully']);

        $this->assertDatabaseHas('transactions', [
            'Amount' => 2500,
            'Status' => 'COMPLETED',
            'user_id' => $tenant->id,
        ]);
    }

    public function test_transaction_requires_amount_and_name(): void
    {
        Sanctum::actingAs($this->makeTenant());

        $this->postJson('/api/transactions', [])
             ->assertUnprocessable();
    }

    public function test_unauthenticated_user_cannot_create_transaction(): void
    {
        $this->postJson('/api/transactions', ['amount' => 100, 'name' => 'Test'])
             ->assertUnauthorized();
    }

    public function test_mail_is_sent_when_application_id_provided(): void
    {
        Mail::fake();

        $landlord    = $this->makeLandlord();
        $tenant      = $this->makeTenant();
        $property    = $this->makeProperty($landlord);

        $application = \App\Models\PropertyApplication::create([
            'property_id'   => $property->id,
            'user_id'       => $tenant->id,
            'status'        => 'payment_requested',
            'message'       => 'I would like to rent.',
            'move_in_date'  => now()->addMonth()->toDateString(),
            'lease_duration' => 12,
        ]);

        Sanctum::actingAs($tenant);

        $this->postJson('/api/transactions', [
            'amount' => $property->price,
            'name'   => $tenant->name,
            'data'   => ['application_id' => $application->id],
        ]);

        Mail::assertSent(\App\Mail\PaymentReceivedLandlord::class);
    }
}
