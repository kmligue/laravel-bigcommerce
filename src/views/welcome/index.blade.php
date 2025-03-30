<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center h-screen bg-gray-100">
    <div class="text-center">
        <h1 class="text-4xl font-bold text-gray-800 mb-4">Welcome to Our Website</h1>
        <p class="text-gray-600 mb-6">We are glad to have you here!</p>
        <form method="post" action="/{{ $storeHash }}/welcome">
            <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg shadow-md hover:bg-blue-700 transition">Get Started</button>
        </form>
    </div>
</body>
</html>
