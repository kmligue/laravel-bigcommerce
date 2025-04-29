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
<x-slot:footer>
<x-mail::footer>
© {{ date('Y') }} <a href="https://limonlabs.dev">Limon Labs.</a> {{ __('All rights reserved.') }}
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>

