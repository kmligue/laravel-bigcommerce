<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ $appName }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300;400;600&display=swap" rel="stylesheet" />
    @php
        $manifestPath = file_exists(public_path('vendor/limonlabs/bigcommerce/manifest.json'))
            ? public_path('vendor/limonlabs/bigcommerce/manifest.json')
            : dirname(__DIR__, 2) . '/dist/manifest.json';
        $assetBase = file_exists(public_path('vendor/limonlabs/bigcommerce/manifest.json'))
            ? asset('vendor/limonlabs/bigcommerce')
            : url('/vendor/limonlabs/bigcommerce');
        $manifest = file_exists($manifestPath) ? json_decode(file_get_contents($manifestPath), true) : [];
        $entry = $manifest['resources/js/main.tsx'] ?? null;
    @endphp
    @if ($entry)
        @if (!empty($entry['css']))
            @foreach ($entry['css'] as $css)
                <link rel="stylesheet" href="{{ $assetBase }}/{{ $css }}" />
            @endforeach
        @endif
    @endif
    <script>
        window.__APP__ = {
            appName: @json($appName),
            stripeKey: @json($stripeKey),
            storeHash: @json($storeHash),
        };
    </script>
</head>
<body class="bg-[#f6f7f9]">
    <div id="root"></div>
    @if ($entry)
        <script type="module" src="{{ $assetBase }}/{{ $entry['file'] }}"></script>
    @endif
</body>
</html>
