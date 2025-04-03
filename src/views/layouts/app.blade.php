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
        <!-- Display upgrade information if on trial -->
        @php
            $status = tenant()->getPlanStatus();
            // Use floor() to ensure we get whole days rather than decimals
            $daysLeft = $status['is_on_trial'] ? now()->diffInDays($status['trial_ends_at'], false) : 0;
            // Make sure we show 0 days if trial has ended
            $daysLeft = max(0, $daysLeft);
            $trialEnded = $status['is_on_trial'] && now()->greaterThanOrEqualTo($status['trial_ends_at']);
            $planName = $status['post_trial_plan'] ? ucfirst($status['post_trial_plan']) : 'Basic';
        @endphp

        @if($status['is_on_trial'] || $trialEnded)
        <div class="w-full bg-gradient-to-r {{ $trialEnded ? 'from-red-100 to-red-50' : ($daysLeft <= 3 ? 'from-amber-100 to-amber-50' : 'from-blue-100 to-blue-50') }} rounded-lg shadow-sm mb-6">
            <div class="px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        @if($trialEnded)
                            <div class="flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-medium text-red-800">Your trial has ended</h3>
                                <p class="text-sm text-red-700">Your account has been downgraded to the {{ $planName }} plan.</p>
                            </div>
                        @elseif($daysLeft <= 3)
                            <div class="flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-medium text-amber-800">Your trial is ending soon</h3>
                                <p class="text-sm text-amber-700">You have <span class="font-semibold">{{ floor($daysLeft) }}</span> {{ Str::plural('day', $daysLeft) }} left in your trial.</p>
                            </div>
                        @else
                            <div class="flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-medium text-blue-800">You're on a free trial</h3>
                                <p class="text-sm text-blue-700">You have <span class="font-semibold">{{ floor($daysLeft) }}</span> {{ Str::plural('day', $daysLeft) }} left with advanced features.</p>
                            </div>
                        @endif
                    </div>
                    <div>
                        @if(!$trialEnded)
                            <a href="/{{ tenant()->store_hash }}/billing" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white {{ $daysLeft <= 3 ? 'bg-amber-600 hover:bg-amber-700' : 'bg-blue-600 hover:bg-blue-700' }} transition-colors duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 {{ $daysLeft <= 3 ? 'focus:ring-amber-500' : 'focus:ring-blue-500' }}">
                                Upgrade Now
                            </a>
                        @else
                            <a href="/{{ tenant()->store_hash }}/billing" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 transition-colors duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                Restore Access
                            </a>
                        @endif
                    </div>
                </div>
                
                @if(!$trialEnded)
                    <div class="mt-3">
                        <div class="relative pt-1">
                            <div class="overflow-hidden h-2 text-xs flex rounded bg-{{ $daysLeft <= 3 ? 'amber' : 'blue' }}-200">
                                @php
                                    // Calculate trial length in days (whole number) and progress
                                    $trialStart = tenant()->trial_ends_at->copy()->subDays(14); // Assuming 14-day trial
                                    $trialLength = 14; // Fixed trial length
                                    $daysPassed = $trialLength - $daysLeft;
                                    $percentComplete = $trialLength > 0 ? ($daysPassed / $trialLength) * 100 : 0;
                                    // Ensure percent is between 0 and 100
                                    $percentComplete = max(0, min(100, $percentComplete));
                                @endphp
                                <div style="width: {{ $percentComplete }}%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center {{ $daysLeft <= 3 ? 'bg-amber-500' : 'bg-blue-500' }}"></div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        @endif

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
