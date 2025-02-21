@extends('limonlabs/bigcommerce::layouts.app-admin')

@section('content')
    @include('limonlabs/bigcommerce::layouts.page-title', ['title' => 'Limon Admin'])

    <div class="bg-white shadow-md p-5 mt-8">
        @include('limonlabs/bigcommerce::layouts.flash')

        <table class="border-collapse table-auto w-full text-sm">
            <thead>
                <tr>
                    <th class="border-b border-[#9a9da1] p-4 pl-8 pt-0 pb-3 text-left">Store Hash</th>
                    <th class="border-b border-[#9a9da1] p-4 pl-8 pt-0 pb-3 text-left">Email</th>
                    <th class="border-b border-[#9a9da1] p-4 pl-8 pt-0 pb-3 text-left">Plan</th>
                    <th class="border-b border-[#9a9da1] p-4 pl-8 pt-0 pb-3 text-left">Discount</th>
                    <th class="border-b border-[#9a9da1] p-4 pl-8 pt-0 pb-3 text-left">Plan</th>
                    <th class="border-b border-[#9a9da1] p-4 pl-8 pt-0 pb-3 text-left">Date</th>
                </tr>
            </thead>
            <tbody class="bg-white">
                @foreach ($stores as $store)
                    <tr>
                        <td class="border-b border-[#d1d5db] p-4 pl-8 text-slate-500">{{ $store->store_hash }}</td>
                        <td class="border-b border-[#d1d5db] p-4 pl-8 text-slate-500 w-1/6">{{ $store->user_email }}</td>
                        <td class="border-b border-[#d1d5db] p-4 pl-8 text-slate-500">
                            @if ($store->plan)
                                @if ($store->plan['plan_id'] == Config::get('plans.free.plan_id'))
                                    Free
                                @elseif ($store->plan['plan_id'] == Config::get('plans.bronze.plan_id'))
                                    Bronze (${{ number_format(Config::get('plans.bronze.price'), 2) }}/month)
                                @elseif ($store->plan['plan_id'] == Config::get('plans.silver.plan_id'))
                                    Silver (${{ number_format(Config::get('plans.silver.price'), 2) }}/month)
                                @elseif ($store->plan['plan_id'] == Config::get('plans.gold.plan_id'))
                                    Gold (${{ number_format(Config::get('plans.gold.price'), 2) }}/month)
                                @endif
                            @endif
                        </td>
                        <td class="border-b border-[#d1d5db] p-4 pl-8 text-slate-500">
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
                        </td>
                        <td class="border-b border-[#d1d5db] p-4 pl-8 text-slate-500">
                            <div class="flex items-center gap-4">
                                @php
                                    $plans = Config::get('plans');
                                @endphp

                                <select class="border plan" data-current-plan="{{ $store->plan['plan_id'] }}" data-store-hash="{{ $store->store_hash }}">
                                    @foreach ($plans as $key => $plan)
                                        <option value="{{ $key }}" {{ ($store->plan && $store->plan['plan_id'] == $plan['plan_id']) ? 'selected' : '' }} data-plan="{{ $plan['plan_id'] }}">
                                            {{ ucfirst($key) }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" class="plan-change border px-1 disabled:bg-[#eee]" disabled>Change</button>
                            </div>
                        </td>
                        <td class="border-b border-[#d1d5db] p-4 pl-8 text-slate-500">{{ $store->created_at->format('F d, Y') }}</td>
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
    });
</script>
@endsection
