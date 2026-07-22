<x-mail::message>
# Application Submitted

Hello {{ $application->user->name }},

Your application for **{{ $application->property->title }}** has been received. We'll notify you once the landlord has reviewed it.

**Application Details**
- Property: {{ $application->property->title }}
- Location: {{ $application->property->town?->name }}, {{ $application->property->district?->name }}
- Price: K{{ number_format($application->property->price, 0) }} / {{ $application->property->listing_type === 'rent' ? 'month' : 'purchase' }}
- Submitted: {{ $application->created_at->format('d M Y, H:i') }}

You can track your application status from your dashboard.

<x-mail::button :url="route('tenant.applications.index')">
View My Applications
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
