<?php

namespace Tests\Feature\Payments;

use App\Services\Payments\PaymentMethodSimulator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PaymentMethodSimulatorTest extends TestCase
{
    use RefreshDatabase;

    private PaymentMethodSimulator $simulator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->simulator = new PaymentMethodSimulator();
    }

    // ── Mobile Money ─────────────────────────────────────────────────────────

    public function test_mtn_mobile_money_returns_correct_structure(): void
    {
        $result = $this->simulator->charge(['method' => 'mobile_money', 'provider' => 'mtn', 'phone' => '0971234567']);

        $this->assertEquals('MOBILE_MONEY', $result['type']);
        $this->assertEquals('mtn', $result['display']['provider']);
        $this->assertEquals('4567', $result['display']['phone_last4']);
    }

    public function test_airtel_mobile_money_is_accepted(): void
    {
        $result = $this->simulator->charge(['method' => 'mobile_money', 'provider' => 'airtel', 'phone' => '0961112222']);

        $this->assertEquals('MOBILE_MONEY', $result['type']);
        $this->assertEquals('airtel', $result['display']['provider']);
    }

    public function test_zamtel_mobile_money_is_accepted(): void
    {
        $result = $this->simulator->charge(['method' => 'mobile_money', 'provider' => 'zamtel', 'phone' => '0951112222']);

        $this->assertEquals('MOBILE_MONEY', $result['type']);
        $this->assertEquals('zamtel', $result['display']['provider']);
    }

    public function test_invalid_mobile_money_provider_throws_validation_exception(): void
    {
        $this->expectException(ValidationException::class);

        $this->simulator->charge(['method' => 'mobile_money', 'provider' => 'paypal', 'phone' => '0971234567']);
    }

    public function test_too_short_phone_throws_validation_exception(): void
    {
        $this->expectException(ValidationException::class);

        $this->simulator->charge(['method' => 'mobile_money', 'provider' => 'mtn', 'phone' => '123']);
    }

    public function test_mobile_money_display_never_exposes_full_phone(): void
    {
        $phone  = '0971234567';
        $result = $this->simulator->charge(['method' => 'mobile_money', 'provider' => 'mtn', 'phone' => $phone]);

        $displayKeys = array_keys($result['display']);
        $this->assertNotContains('phone', $displayKeys);
        $this->assertNotContains('full_phone', $displayKeys);

        // The phone_last4 must not equal the full phone number.
        $this->assertNotEquals($phone, $result['display']['phone_last4']);
        $this->assertEquals(4, strlen($result['display']['phone_last4']));
    }

    // ── Card ─────────────────────────────────────────────────────────────────

    public function test_visa_card_returns_correct_structure(): void
    {
        $result = $this->simulator->charge([
            'method'          => 'card',
            'card_number'     => '4111111111111111',
            'card_expiry'     => '12/28',
            'card_cvv'        => '123',
            'cardholder_name' => 'Test User',
        ]);

        $this->assertEquals('CARD', $result['type']);
        $this->assertEquals('visa', $result['display']['brand']);
        $this->assertEquals('1111', $result['display']['last4']);
    }

    public function test_mastercard_number_resolves_to_mastercard_brand(): void
    {
        $result = $this->simulator->charge([
            'method'          => 'card',
            'card_number'     => '5500005555555559',
            'card_expiry'     => '06/27',
            'card_cvv'        => '321',
            'cardholder_name' => 'Jane Doe',
        ]);

        $this->assertEquals('mastercard', $result['display']['brand']);
    }

    public function test_missing_card_number_throws_validation_exception(): void
    {
        $this->expectException(ValidationException::class);

        $this->simulator->charge([
            'method'          => 'card',
            'card_expiry'     => '12/28',
            'card_cvv'        => '123',
            'cardholder_name' => 'Test User',
        ]);
    }

    public function test_invalid_expiry_format_throws_validation_exception(): void
    {
        $this->expectException(ValidationException::class);

        $this->simulator->charge([
            'method'          => 'card',
            'card_number'     => '4111111111111111',
            'card_expiry'     => '2028-12',
            'card_cvv'        => '123',
            'cardholder_name' => 'Test User',
        ]);
    }

    public function test_invalid_cvv_throws_validation_exception(): void
    {
        $this->expectException(ValidationException::class);

        $this->simulator->charge([
            'method'          => 'card',
            'card_number'     => '4111111111111111',
            'card_expiry'     => '12/28',
            'card_cvv'        => '12',
            'cardholder_name' => 'Test User',
        ]);
    }

    public function test_card_display_never_exposes_sensitive_data(): void
    {
        $result = $this->simulator->charge([
            'method'          => 'card',
            'card_number'     => '4111111111111111',
            'card_expiry'     => '12/28',
            'card_cvv'        => '123',
            'cardholder_name' => 'Test User',
        ]);

        $displayKeys = array_keys($result['display']);
        $this->assertNotContains('card_number', $displayKeys);
        $this->assertNotContains('card_cvv', $displayKeys);
        $this->assertNotContains('card_expiry', $displayKeys);
        $this->assertNotContains('cardholder_name', $displayKeys);

        // last4 must be exactly 4 digits and not the full PAN.
        $this->assertMatchesRegularExpression('/^\d{4}$/', $result['display']['last4']);
        $this->assertNotEquals('4111111111111111', $result['display']['last4']);
    }

    // ── Method dispatching ────────────────────────────────────────────────────

    public function test_unknown_method_throws_validation_exception(): void
    {
        $this->expectException(ValidationException::class);

        $this->simulator->charge(['method' => 'cheque']);
    }

    public function test_missing_method_key_throws_validation_exception(): void
    {
        $this->expectException(ValidationException::class);

        $this->simulator->charge(['provider' => 'mtn', 'phone' => '0971234567']);
    }
}
