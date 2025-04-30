<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="config('app.url')">
{{ config('app.name') }}
</x-mail::header>
</x-slot:header>

# Site Uninstall

Store Hash: **{{ str_replace('stores/', '', $storeInfo->store_hash) }}**

Store Name: **{{ trim($storeInfo->name) }}**

Email: **{{ trim($storeInfo->user_email) }}**

@if ($storeInfo->getPlanStatus()['current_plan'])
Plan: **{{ $storeInfo->getPlanStatus()['current_plan'] }}**
@endif

@if ($storeInfo->getPlanStatus()['trial_ends_at'])
Trial Ends At: **{{ $storeInfo->trial_ends_at ? $storeInfo->trial_ends_at->format('F d, Y') : 'N/A' }}**
@endif

{{-- Footer --}}
@include('limonlabs/bigcommerce::mail.partial.footer')

</x-mail::layout>


