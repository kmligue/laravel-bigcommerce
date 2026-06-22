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
    <style>
        body {
            font-family: "Source Sans Pro", "Helvetica Neue", Arial, sans-serif;
        }
    </style>
    @yield('head')
</head>
<body class="bg-[#f6f7f9]">
    <div class="p-10 mx-auto" style="max-width: 1300px;">
        @yield('content')
    </div>
    <div class="pl-10 pr-10 pb-10 pt-0 mx-auto" style="max-width: 1300px;">
        <div>COPYRIGHT &copy; {{ date('Y') }} <a href="https://limonlabs.dev/" target="_blank" class="text-blue-600">Limon Labs</a>. ALL RIGHTS RESERVED</div>
    </div>
    @yield('footer')
</body>
</html>
