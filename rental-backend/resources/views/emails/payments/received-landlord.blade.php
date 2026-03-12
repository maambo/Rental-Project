<x-mail::message>
# Payment Received

Hello,

A payment of **K{{ number_format($amount, 2) }}** has been received for property: **{{ $propertyName }}**.

**Transaction Details:**
- Transaction ID: {{ $transactionId }}
- Date: {{ $date }}
- Tenant: {{ $tenantName }}

<x-mail::button :url="route('landlord.dashboard')">
View My Dashboard
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
