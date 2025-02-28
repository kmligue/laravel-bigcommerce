<x-mail::message>
Hello {{ $storeInfo->first_name }},

Thank you for installing {{ config('app.name') }}. Your account has been successfully set up, and you can now begin using the application.

If you have any questions during setup or use, our support team is available to assist you.

We look forward to helping you better understand and engage with your customers.

Regards,<br>
The {{ config('app.name') }} Team
</x-mail::message>
