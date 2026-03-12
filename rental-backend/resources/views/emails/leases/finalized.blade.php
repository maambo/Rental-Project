<x-mail::message>
# Lease Finalized

Hello,

Good news! The lease agreement for **{{ $propertyName }}** has been signed and finalized by both parties.

The property is now officially rented. You can find the signed lease agreement in your dashboard documents.

<x-mail::button :url="route('dashboard')">
View Lease Agreement
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
