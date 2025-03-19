<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Page</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center h-screen">
    <div class="text-center max-w-lg">
        <img src="{{ asset('/images/limonlabs/logo.png') }}" alt="Limon Labs Logo" class="mx-auto mb-4">

        <h1 class="text-4xl font-bold">We’re Under Maintenance</h1>
        <p class="mt-4">We apologize for the incovenience, but our application is currently undergroing scheduled maintenance. We'll be back shortly.</p>
        <p class="mt-4">If you need immediate help, please email us at: <a href="mailto:support@limonlabs.dev" class="hover:underline">support@limonlabs.dev</a></p>
        
        <p class="mt-6">— Limon Labs</p>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            // reload page every minute
            setInterval(function() {
                location.reload();
            }, 60000);
        });
    </script>
</body>
</html>
