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
                                    Bronze
                                @elseif ($store->plan['plan_id'] == Config::get('plans.silver.plan_id'))
                                    Silver
                                @elseif ($store->plan['plan_id'] == Config::get('plans.gold.plan_id'))
                                    Gold
                                @endif
                            @endif
                        </td>
                        <td class="border-b border-[#d1d5db] p-4 pl-8 text-slate-500">{{ $store->created_at->format('F d, Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
