<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="config('app.url')">
{{ config('app.name') }}
</x-mail::header>
</x-slot:header>

Name: {{ trim($data['name']) }}

Email: {{ trim($data['email']) }}

Message: {{ trim($data['message']) }}

{{-- Footer --}}
@include('limonlabs/bigcommerce::mail.partial.footer')

</x-mail::layout>

