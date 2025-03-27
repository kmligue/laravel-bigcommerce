@extends('limonlabs/bigcommerce::layouts.app-admin')

@section('content')
    @include('limonlabs/bigcommerce::layouts.page-title', ['title' => 'Limon Admin / ' . config('app.name') . ' / Unified Billing'])

    <div class="bg-white shadow-md p-5 mt-8">
        @include('limonlabs/bigcommerce::layouts.flash')

        <form method="post" action="/limonadmin/unified-billing/create">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Create</button>
        </form>
    </div>
@endsection