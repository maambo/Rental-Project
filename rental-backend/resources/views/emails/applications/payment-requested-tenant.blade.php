<x-mail::message>
# Payment Required

Hello {{ $application->user->name }},

Great news! The landlord has reviewed your application for **{{ $application->property->title }}** and everything looks good.

**Your next step is to complete the payment to secure this property.**

**Payment Details**
- Property: {{ $application->property->title }}
- Amount: K{{ number_format($application->property->price, 0) }}
- Type: {{ ucfirst($application->property->listing_type) }}

> ⚠️ **Act fast** — payment is due by **{{ $application->payment_deadline?->format('d M Y, H:i') ?? 'the deadline set by the landlord' }}**. After this time the application will be automatically rejected. The property is secured once payment is confirmed.

<x-mail::button :url="route('tenant.applications.index')">
Go to Dashboard & Pay Now
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
