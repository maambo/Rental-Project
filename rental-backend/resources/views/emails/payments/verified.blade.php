<x-mail::message>
# Payment Verified

Hello,

The payment for **{{ $propertyName }}** has been verified by the landlord.

**Details:**
- **Amount:** K{{ number_format($amount, 2) }}
- **Date Verified:** {{ $date }}

Thank you for choosing our platform.

<x-mail::button :url="route('dashboard')">
Go to Dashboard
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
