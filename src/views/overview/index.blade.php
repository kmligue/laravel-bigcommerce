@extends('limonlabs/bigcommerce::layouts.app')

@section('content')
    @include('limonlabs/bigcommerce::layouts.page-title', ['title' => 'Overview'])

    <div class="bg-white shadow-md p-5 mt-8">
        @if (request()->has('action') && request()->get('action') == 'upgrade')
            @if (request()->has('success') && request()->get('success') == 'true')
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Success!</strong>
                    <span class="block sm:inline">Your plan has been upgraded.</span>
                </div>
            @else
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Error!</strong>
                    <span class="block sm:inline">There was an error upgrading your plan.</span>
                </div>
            @endif
        @endif
    </div>
@endsection
