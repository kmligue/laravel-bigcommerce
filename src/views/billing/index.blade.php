@extends('limonlabs/bigcommerce::layouts.app')

@section('head')
    <style>
        .pricing-plan {
            min-height: 475px;
        }

        .pricing-plan:hover .pricing-amount {
            background-color: #4c51bf;
            color: #fff;
        }
        
        .trial-badge {
            position: absolute;
            top: -10px;
            right: 10px;
            background-color: #10B981;
            color: white;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            z-index: 10;
        }
    </style>
@endsection

@section('content')
    @include('limonlabs/bigcommerce::billing.partials.tabs')

    @php
        $forceBillingDisplay = true;
    @endphp
    @include('limonlabs/bigcommerce::layouts.free-trial-notice', ['forceBillingDisplay' => true])

    @include('limonlabs/bigcommerce::layouts.page-title', ['title' => 'Pricing Plans'])

    @php
        $plans = Config::get('plans');
        $hasActiveSubscription = tenant()->subscription('default') && tenant()->subscription('default')->active();
    @endphp

    <div class="bg-white shadow-md p-5 mt-8">
        <div class="pricing-table-2 py-6 md:py-12">
            <div class="container mx-auto px-4">

                <div class="pricing-plans lg:flex lg:-mx-4 mt-6 md:mt-12">
                    @php
                        $currentPlan = tenant()->plan;
                        $userStatus = tenant()->getPlanStatus();
                        $isOnTrial = $userStatus['is_on_trial'] ?? false;
                        $hasAdvancedDuringTrial = $userStatus['has_advanced_during_trial'] ?? false;
                    @endphp

                    @foreach ($plans as $key => $plan)
                        @if ($plan['show'])
                        <div class="pricing-plan-wrap lg:w-1/3 my-4 md:my-6">
                                @php
                                    // Determine if this is the current plan, handling both product ID and price ID formats
                                    $isPlanCurrent = false;
                                    
                                    // Check if current plan exists and has a plan_id
                                    if ($currentPlan && isset($currentPlan['plan_id'])) {
                                        $planId = $plan['plan_id'] ?? '';
                                        $currentPlanId = $currentPlan['plan_id'] ?? '';
                                        
                                        // Direct ID match
                                        if ($planId == $currentPlanId) {
                                            $isPlanCurrent = true;
                                        }
                                        // If one is product_id and the other is price_id, check the prefix
                                        elseif (
                                            (strpos($planId, 'prod_') === 0 && strpos($currentPlanId, 'price_') === 0) ||
                                            (strpos($planId, 'price_') === 0 && strpos($currentPlanId, 'prod_') === 0)
                                        ) {
                                            // This is a simplified check - in a real implementation, you'd query Stripe
                                            // to verify that the price belongs to the product
                                            $isPlanCurrent = true;
                                        }
                                    }
                                    
                                    // Handle trial plans - during trial users get the highest available plan
                                    if ($isOnTrial) {
                                        $highestPlan = get_highest_available_plan();
                                        if ($key === $highestPlan) {
                                            $isPlanCurrent = true;
                                        }
                                    }
                                @endphp
                                <div class="pricing-plan border border-indigo-600 border-solid text-center max-w-sm mx-auto transition-colors duration-300 relative
                                    {{ $isPlanCurrent ? 'bg-indigo-700 text-white' : 'bg-slate-50' }}" 
                                    style="min-height: 565px;">
                                    
                                    @if ($isOnTrial && $userStatus['current_plan'] == $key)
                                        <div class="trial-badge">Free Trial</div>
                                    @endif
                                    
                                    <div class="p-6 md:py-8">
                                        <h4 class="font-medium leading-tight text-2xl mb-2">{{ ucfirst($key) }}</h4>
                                    </div>
                                    <div class="pricing-amount p-6 transition-colors duration-300 
                                        {{ $isPlanCurrent ? 'bg-indigo-600' : 'bg-indigo-100' }}">
                                        <div class=""><span class="text-4xl font-semibold">${{ $plan['price'] }}</span> /month</div>
                                    </div>
                                    <div class="p-6">
                                        <ul class="leading-loose">
                                            @foreach ($plan['features'] as $feature)
                                                <li>{{ $feature }}</li>
                                            @endforeach
                                        </ul>
                                        <div class="mt-6 py-4">
                                            @if (!$isOnTrial && $isPlanCurrent)
                                                <div>
                                                    <a href="javascript:;" class="bg-indigo-600 text-xl text-white py-2 px-6 rounded transition-colors duration-300 mb-2 block" disabled>Current</a>
                                                    
                                                    @if($hasActiveSubscription)
                                                    <form method="post" action="{{ url('api/' . $storeHash . '/billing/cancel') }}" class="cancel-subscription-form mt-3">
                                                        <button type="button" class="inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 cancel-subscription-button">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                            Cancel Subscription
                                                        </button>
                                                    </form>
                                                    @endif
                                                </div>
                                            @else
                                                <form method="post" action="{{ url('api/' . $storeHash . '/billing/'. $key .'/select') }}" class="cancel-form">
                                                    @php
                                                        // Determine button text based on user's subscription status
                                                        $buttonText = 'Sign Up';
                                                        
                                                        // If user already has a paid plan, use "Change" instead of "Upgrade/Downgrade"
                                                        if ($currentPlan && isset($currentPlan['plan_id']) && $currentPlan['plan_id'] != Config::get('plans.free.plan_id', '')) {
                                                            $buttonText = 'Change';
                                                        }
                                                        
                                                        // Override: If this is the user's current trial plan, always use "Sign Up"
                                                        if ($isOnTrial && $userStatus['current_plan'] == $key) {
                                                            $buttonText = 'Sign Up';
                                                        }
                                                        
                                                        // Determine button color class
                                                        $buttonColorClass = 'bg-slate-400';
                                                        
                                                        // If this is the current plan (either on trial or paid), use a highlighted color
                                                        if ($isPlanCurrent) {
                                                            $buttonColorClass = 'bg-indigo-600 hover:bg-indigo-700';
                                                        }
                                                    @endphp
                                                    <button type="button" class="{{ $buttonColorClass }} text-xl text-white py-2 px-6 rounded transition-colors duration-300 cancel-button">{{ $buttonText }}</button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach

                </div>

            </div>
        </div>
    </div>
@endsection

@section('footer')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $('document').ready(function() {
            $('.cancel-button').on('click', function(e) {
                e.preventDefault();

                var self = this;

                Swal.fire({
                    title: 'Are you sure?',
                    showCancelButton: true,
                    confirmButtonText: 'Yes',
                }).then((result) => {
                    if (result.isConfirmed) {
                        // $(self).closest('form').submit();
                        var form = $(self).closest('form');
                        var url = form.attr('action');

                        $.ajax({
                            url: url,
                            type: 'post',
                            data: form.serialize(),
                            success: function(response) {
                                if (response.success) {
                                    if (response.url) {
                                        window.parent.location.href = response.url;
                                    } else {
                                        window.location.reload();
                                    }
                                }
                            },
                            error: function(response) {
                                Swal.fire({
                                    title: 'Error',
                                    text: 'An error occurred. Please try again.',
                                    icon: 'error',
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                            }
                        });
                    }
                });
            });
            
            // Handle cancel subscription button
            $('.cancel-subscription-button').on('click', function(e) {
                e.preventDefault();
                
                var self = this;
                
                Swal.fire({
                    title: 'Cancel Subscription',
                    text: 'Are you sure you want to cancel your subscription?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, cancel it!',
                    showDenyButton: true,
                    denyButtonText: 'Cancel at period end',
                    denyButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed || result.isDenied) {
                        var form = $(self).closest('form');
                        var url = form.attr('action');
                        var data = form.serialize();
                        
                        // If user chose to cancel at end of billing period
                        if (result.isDenied) {
                            data += '&end_of_period=1';
                        }
                        
                        $.ajax({
                            url: url,
                            type: 'post',
                            data: data,
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        title: 'Subscription Canceled',
                                        text: response.message,
                                        icon: 'success',
                                        showConfirmButton: false,
                                        timer: 2000
                                    }).then(() => {
                                        window.location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Error',
                                        text: response.message || 'Failed to cancel subscription.',
                                        icon: 'error'
                                    });
                                }
                            },
                            error: function(response) {
                                Swal.fire({
                                    title: 'Error',
                                    text: 'An error occurred. Please try again.',
                                    icon: 'error'
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
