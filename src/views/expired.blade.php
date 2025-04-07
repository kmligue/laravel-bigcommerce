@extends('limonlabs/bigcommerce::layouts.app')

@section('content')
    <div class="w-full bg-gradient-to-r from-red-100 to-red-50 rounded-lg shadow-sm my-6">
        <div class="px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-medium text-red-800">Your plan has expired</h3>
                        <p class="text-sm text-red-700">Please choose a plan to continue using the app.</p>
                    </div>
                </div>
                <div>
                    <a href="/{{ tenant()->store_hash }}/billing" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 transition-colors duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Sign Up
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection