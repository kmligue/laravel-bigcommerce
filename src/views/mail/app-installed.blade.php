<x-mail::message>
# Welcome

Hello {{ $storeInfo->first_name }},

Your Bigcommerce {{ config('app.name') }} app has been successfully installed. You can now start using the app.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
