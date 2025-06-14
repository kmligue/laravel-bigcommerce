<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="config('app.url')">
{{ config('app.name') }}
</x-mail::header>
</x-slot:header>

# New Site Paid Plan

Store Hash: **{{ str_replace('stores/', '', $storeInfo->store_hash) }}**

Store Name: **{{ trim($storeInfo->name) }}**

Email: **{{ trim($storeInfo->user_email) }}**

@if ($storeInfo->getPlanStatus()['current_plan'])
Plan: **{{ $storeInfo->getPlanStatus()['current_plan'] }}**

Paid At: **${{ $storeInfo->getPlanStatus()['plan_details']['price'] }}**
@endif

Date: **{{ $storeInfo->updated_at ? $storeInfo->updated_at->format('F d, Y') : 'N/A' }}**

{{-- Footer --}}
@include('limonlabs/bigcommerce::mail.partial.footer')

</x-mail::layout>



