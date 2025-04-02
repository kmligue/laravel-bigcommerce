@extends('limonlabs/bigcommerce::layouts.app-admin')

@section('head')
    <style>
        .tooltip {
            position: absolute;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .tooltip ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .tooltip li {
            padding: 5px 10px;
            border-bottom: 1px solid #eee;
        }

        .tooltip li:last-child {
            border-bottom: none;
        }

        .info-icon:hover + .tooltip {
            display: block;
        }

        .info-icon + .tooltip {
            top: 0;
            left: 0;
            display: none;
        }
    </style>
@endsection

@section('content')
    @include('limonlabs/bigcommerce::layouts.page-title', ['title' => 'Limon Admin / ' . config('app.name') . ' / Installs'])

    <div class="bg-white shadow-md p-5 mt-8">
        @include('limonlabs/bigcommerce::layouts.flash')

        <table class="border-collapse table-auto w-full text-sm">
            <thead>
                <tr>
                    <th class="border-b border-[#9a9da1] p-4 pl-8 pt-0 pb-3 text-left">Store Hash</th>
                    <th class="border-b border-[#9a9da1] p-4 pl-8 pt-0 pb-3 text-left">Store Name</th>
                    <th class="border-b border-[#9a9da1] p-4 pl-8 pt-0 pb-3 text-left">Name</th>
                    <th class="border-b border-[#9a9da1] p-4 pl-8 pt-0 pb-3 text-left">Plan</th>
                    <th class="border-b border-[#9a9da1] p-4 pl-8 pt-0 pb-3 text-left">Install Date</th>
                    <th class="border-b border-[#9a9da1] p-4 pl-8 pt-0 pb-3 text-left">Expiration Date</th>
                </tr>
            </thead>
            <tbody class="bg-white">
                @foreach ($stores as $store)
                    <tr>
                        <td class="border-b border-[#d1d5db] p-4 pl-8 text-slate-500 store-hash">
                            <div class="relative">
                                <i class="fa-solid fa-circle-info info-icon"></i>
                                <div class="tooltip border p-3 absolute">
                                    <ul>
                                        <li>
                                            name: {{ $store->name }}
                                        </li>
                                        <li>
                                            user_id: {{ $store->user_id }}
                                        </li>
                                        <li>
                                            secure_url: {{ $store->secure_url }}
                                        </li>
                                        <li>
                                            user_email: {{ $store->user_email }}
                                        </li>
                                        <li>
                                            timezone: {{ $store->timezone }}
                                        </li>
                                        <li>
                                            secure_url: {{ $store->secure_url }}
                                        </li>
                                        <li>
                                            status: {{ $store->status }}
                                        </li>
                                        <li>
                                            country: {{ $store->country }}
                                        </li>
                                        <li>
                                            plan_level: {{ $store->plan_level }}
                                        </li>
                                        <li>
                                            multi_storefront_enabled: {{ $store->multi_storefront_enabled ? 'Yes' : 'No' }}
                                        </li>
                                        <li>
                                            discount: 
                                            @php
                                                $subscription = $store->subscription('default');
                                            @endphp
                                            @if ($store->plan)
                                                @if ($store->plan['plan_id'] != Config::get('plans.free.plan_id'))
                                                    @if ($subscription->discount())
                                                        {{ $subscription->discount()->amount_off / 100 }}% off
                                                    @else
                                                        None
                                                    @endif
                                                @else
                                                    None
                                                @endif
                                            @else
                                                None
                                            @endif
                                        </li>
                                    </ul>
                                </div>
                                <a href="{{ get_load_redirect($store->store_hash) }}" class="hover:underline">{{ str_replace('stores/', '', $store->store_hash) }}</a>
                            </div>
                        </td>
                        <td class="border-b border-[#d1d5db] p-4 pl-8 text-slate-500">{{ $store->name }}</td>
                        <td class="border-b border-[#d1d5db] p-4 pl-8 text-slate-500 w-1/6">
                            <div class="flex items-center gap-1">
                                <a href="mailto:{{ $store->user_email }}">
                                    <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M48 64C21.5 64 0 85.5 0 112c0 15.1 7.1 29.3 19.2 38.4L236.8 313.6c11.4 8.5 27 8.5 38.4 0L492.8 150.4c12.1-9.1 19.2-23.3 19.2-38.4c0-26.5-21.5-48-48-48L48 64zM0 176L0 384c0 35.3 28.7 64 64 64l384 0c35.3 0 64-28.7 64-64l0-208L294.4 339.2c-22.8 17.1-54 17.1-76.8 0L0 176z"/></svg>
                                </a> 
                                @php
                                    $name = $store->first_name . ' ' . $store->last_name;

                                    if (empty(trim($name))) {
                                        $name = $store->user_email;
                                    }
                                @endphp
                                <span>{{ $name }}</span>
                            </div>
                        </td>
                        <td class="border-b border-[#d1d5db] p-4 pl-8 text-slate-500">
                            <span class="plan-text">
                                @if ($store->plan)
                                    @php 
                                        $plans = Config::get('plans');
                                    @endphp

                                    @foreach ($plans as $key => $plan) 
                                        @if ($store->plan['plan_id'] == $plan['plan_id'])
                                            {{ ucfirst($key) }}
                                            @if ($plan['price'] > 0)
                                                (${{ number_format($plan['price'], 2) }}/month)
                                            @endif
                                        @endif
                                    @endforeach
                                @else
                                    -
                                @endif
                            </span>

                            <div class="flex items-center gap-2 plan-edit" style="display: none;">
                                @php
                                    $plans = Config::get('plans');
                                @endphp

                                <select class="border plan" data-current-plan="{{ $store->plan && $store->plan['plan_id'] }}" data-store-hash="{{ $store->store_hash }}">
                                    <option value="" data-plan=''>-</option>
                                    @foreach ($plans as $key => $plan)
                                        <option value="{{ $key }}" {{ ($store->plan && $store->plan['plan_id'] == $plan['plan_id']) ? 'selected' : '' }} data-plan="{{ $plan['plan_id'] }}">
                                            {{ ucfirst($key) }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" class="plan-change border px-1 disabled:bg-[#eee]" disabled>Change</button>
                                <button type="button" class="plan-cancel disabled:bg-[#eee]">
                                    <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"/></svg>
                                </button>
                            </div>

                            <button type="button" class="plan-edit-btn" title="Edit">
                                <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M410.3 231l11.3-11.3-33.9-33.9-62.1-62.1L291.7 89.8l-11.3 11.3-22.6 22.6L58.6 322.9c-10.4 10.4-18 23.3-22.2 37.4L1 480.7c-2.5 8.4-.2 17.5 6.1 23.7s15.3 8.5 23.7 6.1l120.3-35.4c14.1-4.2 27-11.8 37.4-22.2L387.7 253.7 410.3 231zM160 399.4l-9.1 22.7c-4 3.1-8.5 5.4-13.3 6.9L59.4 452l23-78.1c1.4-4.9 3.8-9.4 6.9-13.3l22.7-9.1 0 32c0 8.8 7.2 16 16 16l32 0zM362.7 18.7L348.3 33.2 325.7 55.8 314.3 67.1l33.9 33.9 62.1 62.1 33.9 33.9 11.3-11.3 22.6-22.6 14.5-14.5c25-25 25-65.5 0-90.5L453.3 18.7c-25-25-65.5-25-90.5 0zm-47.4 168l-144 144c-6.2 6.2-16.4 6.2-22.6 0s-6.2-16.4 0-22.6l144-144c6.2-6.2 16.4-6.2 22.6 0s6.2 16.4 0 22.6z"/></svg>
                            </button>
                        </td>
                        <td class="border-b border-[#d1d5db] p-4 pl-8 text-slate-500">{{ $store->created_at->format('F d, Y') }}</td>
                        <td class="border-b border-[#d1d5db] p-4 pl-8 text-slate-500">
                            <span class="trial-end-text">
                                {{ $store->trial_ends_at ? $store->trial_ends_at->format('F d, Y') : 'N/A' }}
                            </span>

                            <div class="flex items-center gap-2 trial-end-edit" style="display: none;">
                                <input type="date" class="border trial" value="{{ $store->onTrial() ? $store->trial_ends_at->format('Y-m-d') : '' }}" data-store-hash="{{ $store->store_hash }}">
                                <button type="button" class="trial-change border px-1">Change</button>
                                <button type="button" class="trial-end-cancel disabled:bg-[#eee]">
                                    <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"/></svg>
                                </button>
                            </div>

                            <button type="button" class="trial-end-edit-btn" title="Edit">
                                <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M410.3 231l11.3-11.3-33.9-33.9-62.1-62.1L291.7 89.8l-11.3 11.3-22.6 22.6L58.6 322.9c-10.4 10.4-18 23.3-22.2 37.4L1 480.7c-2.5 8.4-.2 17.5 6.1 23.7s15.3 8.5 23.7 6.1l120.3-35.4c14.1-4.2 27-11.8 37.4-22.2L387.7 253.7 410.3 231zM160 399.4l-9.1 22.7c-4 3.1-8.5 5.4-13.3 6.9L59.4 452l23-78.1c1.4-4.9 3.8-9.4 6.9-13.3l22.7-9.1 0 32c0 8.8 7.2 16 16 16l32 0zM362.7 18.7L348.3 33.2 325.7 55.8 314.3 67.1l33.9 33.9 62.1 62.1 33.9 33.9 11.3-11.3 22.6-22.6 14.5-14.5c25-25 25-65.5 0-90.5L453.3 18.7c-25-25-65.5-25-90.5 0zm-47.4 168l-144 144c-6.2 6.2-16.4 6.2-22.6 0s-6.2-16.4 0-22.6l144-144c6.2-6.2 16.4-6.2 22.6 0s6.2 16.4 0 22.6z"/></svg>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

@section('footer')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $('select.plan').on('change', function(e) {
            var currentPlan = $(this).data('current-plan');
            var selectedPlan = $(this).find(':selected').data('plan');
            
            if (currentPlan != selectedPlan) {
                $(this).next().prop('disabled', false);
            } else {
                $(this).next().prop('disabled', true);
            }
        });

        $('.plan-change').on('click', function(e) {
            e.preventDefault();

            // Add loading spinner on button
            $(this).html('Change <i class="fas fa-spinner fa-spin"></i>');

            var storeHash = $(this).prev().data('store-hash');
            var plan = $(this).prev().val();
            var self = this;

            $.ajax({
                url: '/api/' + storeHash + '/billing/' + plan + '/select',
                type: 'POST',
                success: function(response) {
                    if (response.success) {
                        if (response.url) {
                            // This returns a stripe url. Which means user needs to enter their card details
                            // For now, we will just show an error message
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Customer needs to enter their card details to proceed'
                            });

                            $(self).html('Change');
                        } else {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            });

                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });

                        $(self).html('Change');
                    }
                }
            });
        });

        $('.trial-change').on('click', function(e) {
            e.preventDefault();

            // Add loading spinner on button
            $(this).html('Change <i class="fas fa-spinner fa-spin"></i>');

            var storeHash = $(this).prev().data('store-hash');
            var trial = $(this).prev().val();
            var self = this;

            $.ajax({
                url: '/api/' + storeHash + '/billing/trial/change',
                type: 'POST',
                data: {
                    trial: trial
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        });

                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });

                        $(self).html('Change');
                    }
                }
            });
        });

        $('.plan-edit-btn').on('click', function(e) {
            e.preventDefault();

            $(this).hide();
            $(this).parent().find('.plan-text').hide();
            $(this).parent().find('.plan-edit').show();
        });

        $('.plan-cancel').on('click', function(e) {
            e.preventDefault();

            $(this).parent().hide();
            $(this).parent().parent().find('.plan-text').show();
            $(this).parent().parent().find('.plan-edit-btn').show();
        });

        $('.trial-end-edit-btn').on('click', function(e) {
            e.preventDefault();

            $(this).hide();
            $(this).parent().find('.trial-end-text').hide();
            $(this).parent().find('.trial-end-edit').show();
        });

        $('.trial-end-cancel').on('click', function(e) {
            e.preventDefault();

            $(this).parent().hide();
            $(this).parent().parent().find('.trial-end-text').show();
            $(this).parent().parent().find('.trial-end-edit-btn').show();
        });
    });
</script>
@endsection
