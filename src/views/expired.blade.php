@extends('limonlabs/bigcommerce::layouts.app')

@section('content')
    <div class="bg-white shadow-md p-5 mt-8">
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">Your plan has expired. Please <a href="/{{ tenant()->store_hash }}/billing" class="text-[#4B71FC]">upgrade</a> your plan to continue using the app.</span>
        </div>
    </div>
@endsection