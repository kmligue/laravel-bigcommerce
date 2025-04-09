<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center h-screen bg-gray-100">
    <div class="text-center w-[1000px] mx-auto">
        <h1 class="text-4xl font-bold text-gray-800 mb-4">Welcome to {{ config('app.name') }}.</h1>
        <p class="text-gray-600 mb-6">Your BigCommerce store just got an upgrade. Our smart, easy-to-use app is designed to streamline your operations, helping you save time, sell more, and grow your business—all from within BigCommerce.</p>
        <p class="text-gray-600 mb-6">If there are any questions, email us out at <a href="mailto:support@limonlabs.dev" class="text-blue-500 hover:text-blue-600">support@limonlabs.dev</a></p>
        <form method="post" action="/{{ $storeHash }}/welcome">
            <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg shadow-md hover:bg-blue-700 transition">Get Started</button>
        </form>
    </div>
</body>
</html>
