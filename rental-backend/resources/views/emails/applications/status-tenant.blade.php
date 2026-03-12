<x-mail::message>
# Application Status Updated

Hello {{ $user->name }},

The status of your application for **{{ $propertyName }}** has been updated to: **{{ ucfirst($status) }}**.

@if($status === 'approved')
Please log in to your dashboard to review the lease agreement and make your initial payment.
@elseif($status === 'rejected')
Reason: {{ $reason }}
@endif

<x-mail::button :url="route('dashboard')">
Go to Dashboard
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
