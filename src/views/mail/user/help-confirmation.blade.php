<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="config('app.url')">
{{ config('app.name') }}
</x-mail::header>
</x-slot:header>

Hello {{ $data['name'] }},

Thanks for reaching out to us. We wanted to confirm that we received your Help message and will be looking at it shortly. The information you submitted to us is below:

<x-mail::panel>
**Message:** {{ $data['message'] }}

**Name:** {{ $data['name'] }}  

**Email:** {{ $data['email'] }}  
</x-mail::panel>

Thanks,<br>
Limon Labs Support

{{-- Footer --}}
@include('limonlabs/bigcommerce::mail.partial.footer')

</x-mail::layout>


