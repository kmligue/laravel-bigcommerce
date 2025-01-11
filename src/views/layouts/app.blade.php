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

    @yield('head')
</head>
<body class="bg-[#f6f7f9]">
    <div class="p-10 mx-auto" style="max-width: 1300px;">
        @if (View::exists('layouts/tabs'))
            @include('layouts.tabs')
        @else
            @include('limonlabs/bigcommerce::layouts.tabs')
        @endif

        @yield('content')
    </div>

    <div class="pl-10 pr-10 pb-10 pt-0 mx-auto" style="max-width: 1300px;">
        <div>COPYRIGHT &copy; {{ date('Y') }} <a href="https://limonlabs.dev/" target="_blank" class="text-blue-600">LimonLabs</a>. ALL RIGHTS RESERVED</div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            $('form').on('submit', function(e) {
                $(this).find('button[type="submit"]').html('<div class="flex items-center justify-center">' + $(this).find('button[type="submit"]').eq(0).text() + '<svg class="animate-spin ml-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg></div>');
            });
        });
    </script>
    @yield('footer')
</body>
</html>
