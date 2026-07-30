<?php

namespace Tests\Feature\Payments;

use App\Models\Transaction;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RentalTestSupport;

class BillingDoublePayTest extends TestCase
{
    use RefreshDatabase, RentalTestSupport;

    private PaymentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
        $this->service = app(PaymentService::class);
    }

    public function test_billing_payment_succeeds_for_pending_bill(): void
    {
        $landlord = $this->makeLandlord();
        $tenant   = $this->makeTenant();
        $property = $this->makeProperty($landlord);
        $lease    = $this->makeLeaseAgreement($tenant, $property);
        $billing  = $this->makeBilling($lease);

        $this->service->initiateBillingPayment($billing, $this->mobileMoneyPayload());

        $billing->refresh();
        $this->assertEquals('paid', $billing->status);
        $this->assertNotNull($billing->paid_at);
        $this->assertEquals(1, Transaction::count());
    }

    public function test_paying_an_already_paid_bill_throws_runtime_exception(): void
    {
        $landlord = $this->makeLandlord();
        $tenant   = $this->makeTenant();
        $property = $this->makeProperty($landlord);
        $lease    = $this->makeLeaseAgreement($tenant, $property);
        $billing  = $this->makeBilling($lease, ['status' => 'paid']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('This bill has already been paid.');

        $this->service->initiateBillingPayment($billing, $this->mobileMoneyPayload());
    }

    public function test_double_pay_attempt_creates_no_extra_transaction(): void
    {
        $landlord = $this->makeLandlord();
        $tenant   = $this->makeTenant();
        $property = $this->makeProperty($landlord);
        $lease    = $this->makeLeaseAgreement($tenant, $property);
        $billing  = $this->makeBilling($lease);

        // First payment succeeds.
        $this->service->initiateBillingPayment($billing, $this->mobileMoneyPayload());

        // Second payment throws.
        try {
            $billing->refresh();
            $this->service->initiateBillingPayment($billing, $this->mobileMoneyPayload());
        } catch (\RuntimeException) {
            // expected
        }

        // Only one Transaction row should exist.
        $this->assertEquals(1, Transaction::count());
    }
}
