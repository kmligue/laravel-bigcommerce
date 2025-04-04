<!-- Display upgrade information if on trial -->
@php
    $status = tenant()->getPlanStatus();
    // Use floor() to ensure we get whole days rather than decimals
    $daysLeft = $status['is_on_trial'] ? now()->diffInDays($status['trial_ends_at'], false) : 0;
    // Make sure we show 0 days if trial has ended
    $daysLeft = max(0, $daysLeft);
    $trialEnded = $status['is_on_trial'] && now()->greaterThanOrEqualTo($status['trial_ends_at']);
    $planName = $status['post_trial_plan'] ? ucfirst($status['post_trial_plan']) : ucfirst(get_lowest_available_plan());
    $trialEndDate = isset($status['trial_ends_at']) ? $status['trial_ends_at']->format('F d, Y') : '';
@endphp

@if($status['is_on_trial'] || $trialEnded)
<div class="w-full bg-gradient-to-r {{ $trialEnded ? 'from-red-100 to-red-50' : ($daysLeft <= 3 ? 'from-amber-100 to-amber-50' : 'from-blue-100 to-blue-50') }} rounded-lg shadow-sm my-6">
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
                        <p class="text-sm text-amber-700">{{ floor($daysLeft) }} {{ Str::plural('day', $daysLeft) }} remaining. Free trial ends on {{ $trialEndDate }}.</p>
                    </div>
                @else
                    <div class="flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-medium text-blue-800">You are on free trial of the {{ ucfirst(get_highest_available_plan()) }} plan with access to all its features.</h3>
                        <p class="text-sm text-blue-700">{{ floor($daysLeft) }} {{ Str::plural('day', $daysLeft) }} remaining. Free trial ends on {{ $trialEndDate }}.</p>
                    </div>
                @endif
            </div>
            <div>
                @if(!$trialEnded)
                    <a href="/{{ tenant()->store_hash }}/billing" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white {{ $daysLeft <= 3 ? 'bg-amber-600 hover:bg-amber-700' : 'bg-blue-600 hover:bg-blue-700' }} transition-colors duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 {{ $daysLeft <= 3 ? 'focus:ring-amber-500' : 'focus:ring-blue-500' }}">
                        Sign Up
                    </a>
                @else
                    <a href="/{{ tenant()->store_hash }}/billing" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 transition-colors duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Restore Access
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endif