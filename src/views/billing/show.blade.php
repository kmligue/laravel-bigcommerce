@extends('limonlabs/bigcommerce::layouts.app')

@section('head')
    <style>
        .loader {
            margin: 0 auto;
            border: 2px solid #f3f3f3;
            border-radius: 50%;
            border-top: 2px solid #3498db;
            width: 20px;
            height: 20px;
            -webkit-animation: spin 2s linear infinite; /* Safari */
            animation: spin 2s linear infinite;
        }

        /* Safari */
        @-webkit-keyframes spin {
            0% { -webkit-transform: rotate(0deg); }
            100% { -webkit-transform: rotate(360deg); }
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
@endsection

@section('content')
    @include('limonlabs/bigcommerce::layouts.page-title', ['title' => 'Billing History'])

    <div class="bg-white shadow-md p-5 mt-8 w-1/2 mx-auto">
        <form method="post" action="{{ url('api/billing/' . $plan) }}" id="payment-form">
            @csrf

            <input type="hidden" name="plan" value="{{ $plan }}">

            <input id="card-holder-name" type="text">

            <!-- Stripe Elements Placeholder -->
            <div id="card-element"></div>

            <button id="card-button" type="button" class="bg-blue-700 py-2 px-10 text-white float-right mt-5 w-full" data-secret="{{ $intent->client_secret }}">
                Pay
            </button>

            <div class="clear-both"></div>
        </form>
    </div>
@endsection

@section('footer')
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        const stripe = Stripe('{{ env("STRIPE_KEY") }}');

        const elements = stripe.elements();
        const cardElement = elements.create('card');

        cardElement.mount('#card-element');

        const cardHolderName = document.getElementById('card-holder-name');
        const cardButton = document.getElementById('card-button');
        const clientSecret = cardButton.dataset.secret;
        const form = document.getElementById('payment-form');

        cardButton.addEventListener('click', async (e) => {
            $('#card-button').html('<div class="loader"></div>');

            const { setupIntent, error } = await stripe.confirmCardSetup(
                clientSecret, {
                    payment_method: {
                        card: cardElement,
                        billing_details: { name: cardHolderName.value }
                    }
                }
            );

            if (error) {
                // Display "error.message" to the user...
            } else {
                var hiddenInput = document.createElement('input');
                hiddenInput.setAttribute('type', 'hidden');
                hiddenInput.setAttribute('name', 'paymentMethod');
                hiddenInput.setAttribute('value', setupIntent.payment_method);
                form.appendChild(hiddenInput);

                // Submit the form
                form.submit();
            }
        });
    </script>
@endsection
