<x-mail::message>
# Application Update

Hello {{ $user->name }},

Your application for **{{ $propertyName }}** has been updated.

@if($status === 'under_review')
**Status: Under Review**

The landlord has started reviewing your application. You'll hear back soon.
@elseif($status === 'rejected')
**Status: Not Successful**

Unfortunately your application was not approved.

@if($reason)
**Reason:** {{ $reason }}
@endif
@else
**Status:** {{ ucfirst(str_replace('_', ' ', $status)) }}
@endif

<x-mail::button :url="route('tenant.applications.index')">
View My Applications
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
