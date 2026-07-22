<x-mail::message>
# New Application Received

Hello {{ $application->property->landlord->name }},

You have received a new application for your property **{{ $application->property->title }}**.

**Applicant Details**
- Name: {{ $application->user->name }}
- Email: {{ $application->user->email }}
- Message: {{ $application->message ?? 'No message provided' }}
- Applied: {{ $application->created_at->format('d M Y, H:i') }}

Please log in to review the application and begin the process.

<x-mail::button :url="route('landlord.property-applications.show', $application->id)">
Review Application
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
