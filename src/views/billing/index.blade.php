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
    </style>
@endsection

@section('content')
    @include('limonlabs/bigcommerce::billing.partials.tabs')

    @include('limonlabs/bigcommerce::layouts.page-title', ['title' => 'Pricing Plans'])

    @php
        $plans = Config::get('plans');
    @endphp

    <div class="bg-white shadow-md p-5 mt-8">
        <div class="pricing-table-2 py-6 md:py-12">
            <div class="container mx-auto px-4">

                <div class="pricing-plans lg:flex lg:-mx-4 mt-6 md:mt-12">
                    @php
                        $currentPlan = tenant()->plan;
                    @endphp

                    @foreach ($plans as $key => $plan)
                        @if ($plan['show'])
                        <div class="pricing-plan-wrap lg:w-1/3 my-4 md:my-6">
                                <div class="pricing-plan border border-indigo-600 border-solid text-center max-w-sm mx-auto transition-colors duration-300 {{ (($currentPlan && $currentPlan['plan_id'] == $plan['plan_id'])) ? 'bg-indigo-700 text-white' : 'bg-slate-50' }}" style="min-height: 565px;">
                                    <div class="p-6 md:py-8">
                                        <h4 class="font-medium leading-tight text-2xl mb-2">{{ ucfirst($key) }}</h4>
                                    </div>
                                    <div class="pricing-amount p-6 transition-colors duration-300 {{ (($currentPlan && $currentPlan['plan_id'] == $plan['plan_id'])) ? 'bg-indigo-600' : 'bg-indigo-100' }}">
                                        <div class=""><span class="text-4xl font-semibold">${{ $plan['price'] }}</span> /month</div>
                                    </div>
                                    <div class="p-6">
                                        <ul class="leading-loose">
                                            @foreach ($plan['features'] as $feature)
                                                <li>{{ $feature }}</li>
                                            @endforeach
                                        </ul>
                                        <div class="mt-6 py-4">
                                            @if ($currentPlan && $currentPlan['plan_id'] == $plan['plan_id'])
                                                <a href="javascript:;" class="bg-indigo-600 text-xl text-white py-2 px-6 rounded transition-colors duration-300" disabled>Current</a>
                                            @else
                                                <form method="post" action="{{ url('api/' . $storeHash . '/billing/'. $key .'/select') }}" class="cancel-form">
                                                    <button type="button" class="bg-slate-400 text-xl text-white py-2 px-6 rounded transition-colors duration-300 cancel-button">{{ ($currentPlan['price'] > $plan['price']) ? 'Downgrade' : 'Upgrade' }}</button>
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
        });
    </script>
@endsection
