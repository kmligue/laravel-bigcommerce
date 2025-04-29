<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="config('app.url')">
{{ config('app.name') }}
</x-mail::header>
</x-slot:header>

Name: {{ $data['name'] }}

Email: {{ $data['email'] }}

Message: {{ $data['message'] }}

{{-- Footer --}}
@include('limonlabs/bigcommerce::mail.partial.footer')

</x-mail::layout>

