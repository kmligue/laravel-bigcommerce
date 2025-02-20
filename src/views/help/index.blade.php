@extends('limonlabs/bigcommerce::layouts.app')

@section('content')
    @include('limonlabs/bigcommerce::layouts.page-title', ['title' => 'Help'])

    <div class="bg-white shadow-md p-5 mt-8">
        @include('limonlabs/bigcommerce::layouts.flash')

        @if (request()->query('success') && request()->query('success') == 1)
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Success!</strong>
                <span class="block sm:inline">Your message has been sent successfully.</span>
            </div>
        @endif

        <form action="https://api.staticforms.xyz/submit" method="post">
            <div class="mb-5">
                <label for="name" class="block text-sm">Your Name</label>
                <input type="text" class="w-full border rounded-md p-2 mt-1" name="name" placeholder="Your Name">
            </div>

            <div class="mb-5">
                <label for="name" class="block text-sm">Your Email</label>
                <input type="text" class="w-full border rounded-md p-2 mt-1" name="email" placeholder="Your Email" />
            </div>

            <div class="mb-5">
                <label for="name" class="block text-sm">Message</label>
                <textarea name="message" class="w-full border rounded-md p-2 mt-1" rows="5"></textarea>
            </div>

            <input type="text" name="honeypot" style="display:none">
            <input type="hidden" name="accessKey" value="{{ env('STATICFORMS_ACCESS_KEY') }}">
            <input type="hidden" name="subject" value="Help from - {{ env('APP_NAME') }}" />
            <input type="hidden" name="redirectTo" value="{{ url('/' . $storeHash . '/help?success=1') }}">

            <div class="mb-5 text-right">
                <input type="submit" class="bg-[#4b71fc] text-white px-3 py-1 rounded" value="Submit" />
            </div>
        </form>
    </div>
@endsection
