<x-mail::message>
Hello {{ $storeInfo->first_name }},

We're sorry to see you go, but we are writing to confirm that your uninstall is completed and your subscription has now been cancelled. 

In case you change your mind, it only takes a minute to reinstall {{ config('app.name') }}. Your data will be removed from our systems on {{ now()->addMonth()->format('F d, Y') }}.

We are always here to help. For any questions, please drop up a line at support@limonlabs.dev or visit our Help Center for more information.

Our best,<br>
The {{ config('app.name') }} Team
</x-mail::message>
