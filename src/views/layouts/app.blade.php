<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>{{ config('app.name') }}</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300;400&display=swap" rel="stylesheet"/>

    <script src="https://kit.fontawesome.com/db85af4214.js" crossorigin="anonymous"></script>

    <style>
        body {
            font-family: "Source Sans Pro", "Helvetica Neue", Arial, sans-serif;
        }
    </style>

    @yield('head')
</head>
<body class="bg-[#f6f7f9]">
    <div class="p-10 mx-auto" style="max-width: 1300px;">
        @if (View::exists('layouts/tabs'))
            @include('layouts.tabs')
        @else
            @include('limonlabs/bigcommerce::layouts.tabs')
        @endif

        @if (!request()->is('*/billing*'))
            @include('limonlabs/bigcommerce::layouts.free-trial-notice')
        @endif

        @yield('content')
    </div>

    <div class="pl-10 pr-10 pb-10 pt-0 mx-auto" style="max-width: 1300px;">
        <div>COPYRIGHT &copy; {{ date('Y') }} <a href="https://limonlabs.dev/" target="_blank" class="text-blue-600">Limon Labs</a>. ALL RIGHTS RESERVED</div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            $('form').on('submit', function(e) {
                $(this).find('button[type="submit"]').html('<div class="flex items-center justify-center">' + $(this).find('button[type="submit"]').eq(0).text() + '<svg class="animate-spin ml-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg></div>');
            });
        });
    </script>

    <script>
        // Format dates based on user's browser locale
        document.addEventListener('DOMContentLoaded', function() {
            // Format dates using the user's locale
            const dateElements = document.querySelectorAll('.formatted-date');
            dateElements.forEach(function(element) {
                const isoDate = element.getAttribute('data-date');
                if (isoDate) {
                    try {
                        const date = new Date(isoDate);
                        // Format the date using the user's locale and a user-friendly format
                        const formattedDate = new Intl.DateTimeFormat(navigator.language, {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric'
                        }).format(date);
                        element.textContent = formattedDate;
                    } catch (e) {
                        console.error('Error formatting date:', e);
                    }
                }
            });
            
            // Calculate days left based on user's local timezone
            const trialNotices = document.querySelectorAll('.trial-notice');
            trialNotices.forEach(function(notice) {
                const trialEndDate = notice.getAttribute('data-trial-end');
                if (trialEndDate) {
                    try {
                        // Get today's date at midnight in user's timezone
                        const today = new Date();
                        today.setHours(0, 0, 0, 0);
                        
                        // Get trial end date in user's timezone
                        const endDate = new Date(trialEndDate);
                        endDate.setHours(23, 59, 59, 999); // End of day
                        
                        // Calculate the difference in days
                        const timeDiff = endDate.getTime() - today.getTime();
                        const daysLeft = Math.max(0, Math.floor(timeDiff / (1000 * 60 * 60 * 24)));
                        
                        // Update all days-left elements in this notice
                        const daysLeftElements = notice.querySelectorAll('.days-left');
                        daysLeftElements.forEach(function(el) {
                            el.textContent = daysLeft;
                            el.setAttribute('data-client-days', daysLeft);
                            
                            // Update the plural text for "day"/"days"
                            const nextSibling = el.nextSibling;
                            if (nextSibling && nextSibling.nodeType === Node.TEXT_NODE) {
                                const text = nextSibling.textContent;
                                if (text.includes('day')) {
                                    nextSibling.textContent = ' ' + (daysLeft === 1 ? 'day' : 'days') + ' remaining. ';
                                }
                            }
                            
                            // Update the notice color based on days left
                            if (daysLeft <= 3) {
                                notice.classList.remove('from-blue-100', 'to-blue-50');
                                notice.classList.add('from-amber-100', 'to-amber-50');
                                
                                // Update the button color if it exists
                                const button = notice.querySelector('a[href*="/billing"]');
                                if (button) {
                                    button.classList.remove('bg-blue-600', 'hover:bg-blue-700', 'focus:ring-blue-500');
                                    button.classList.add('bg-amber-600', 'hover:bg-amber-700', 'focus:ring-amber-500');
                                }
                            }
                        });
                    } catch (e) {
                        console.error('Error calculating days left:', e);
                    }
                }
            });
        });
    </script>
    @yield('footer')
</body>
</html>
