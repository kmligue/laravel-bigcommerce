<x-mail::message>
Hello {{ $data['name'] }},

Thanks for reaching out to us. We wanted to confirm that we received your Help message and will be looking at it shortly. The information you submitted to us is below:

<x-mail::panel>
**Message:** {{ $data['message'] }}

**Name:** {{ $data['name'] }}  

**Email:** {{ $data['email'] }}  
</x-mail::panel>

Thanks,<br>
<a href="https://limonlabs.dev/">Limon Labs Support</a>
</x-mail::message> 