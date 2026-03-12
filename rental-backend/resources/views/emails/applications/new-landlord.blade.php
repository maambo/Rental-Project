<x-mail::message>
# New Landlord Application

Hello,

A new user has applied to become a landlord on Rental App.

**Applicant Details:**
- Name: {{ $user->name }}
- Email: {{ $user->email }}

<x-mail::button :url="route('admin.applications.show', $applicationId)">
Review Application
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
