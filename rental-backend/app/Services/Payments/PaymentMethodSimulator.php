<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Stands in for a real payment gateway (PayChangu, mobile money aggregator, card
 * processor, etc.). Validates the shape of the chosen method's input and always
 * returns a successful charge after being called — no gateway is actually contacted.
 *
 * Full card numbers/CVV/expiry and full phone numbers are only ever held in the
 * method arguments here; the return value carries nothing but display-safe data
 * (brand + last 4, provider + last 4), so nothing sensitive reaches the database.
 *
 * Swapping in a real gateway later means replacing this one class (or having it
 * implement a PaymentGatewayInterface) without touching PaymentService, controllers,
 * or the Vue payment UI.
 */
class PaymentMethodSimulator
{
    private const MOBILE_MONEY_PROVIDERS = ['mtn', 'airtel', 'zamtel'];
    private const CARD_BRANDS = ['visa', 'mastercard'];

    /**
     * @param array $payload {
     *     method: 'mobile_money'|'card',
     *     provider?: 'mtn'|'airtel'|'zamtel',
     *     phone?: string,
     *     card_number?: string,
     *     card_expiry?: string,
     *     card_cvv?: string,
     *     cardholder_name?: string,
     * }
     * @return array { type: string, display: array }
     *     type   — value to store in transactions.Type ('MOBILE_MONEY' or 'CARD')
     *     display — non-sensitive data safe to persist in transactions.Data
     */
    public function charge(array $payload): array
    {
        $method = $payload['method'] ?? null;

        return match ($method) {
            'mobile_money' => $this->chargeMobileMoney($payload),
            'card' => $this->chargeCard($payload),
            default => throw ValidationException::withMessages([
                'method' => 'Choose a payment method: mobile money or card.',
            ]),
        };
    }

    private function chargeMobileMoney(array $payload): array
    {
        $validator = Validator::make($payload, [
            'provider' => ['required', 'string', 'in:' . implode(',', self::MOBILE_MONEY_PROVIDERS)],
            'phone' => ['required', 'string', 'regex:/^[0-9+\s]{7,15}$/'],
        ]);

        $validator->validate();

        $phone = preg_replace('/\D/', '', $payload['phone']);

        return [
            'type' => 'MOBILE_MONEY',
            'display' => [
                'provider' => $payload['provider'],
                'phone_last4' => substr($phone, -4),
            ],
        ];
    }

    private function chargeCard(array $payload): array
    {
        $validator = Validator::make($payload, [
            'card_number' => ['required', 'string', 'regex:/^[0-9\s]{12,19}$/'],
            'card_expiry' => ['required', 'string', 'regex:/^(0[1-9]|1[0-2])\/\d{2}$/'],
            'card_cvv' => ['required', 'string', 'regex:/^\d{3,4}$/'],
            'cardholder_name' => ['required', 'string', 'max:120'],
        ]);

        $validator->validate();

        $cardNumber = preg_replace('/\D/', '', $payload['card_number']);
        $brand = str_starts_with($cardNumber, '4') ? 'visa' : 'mastercard';

        return [
            'type' => 'CARD',
            'display' => [
                'brand' => $brand,
                'last4' => substr($cardNumber, -4),
            ],
        ];
    }
}
