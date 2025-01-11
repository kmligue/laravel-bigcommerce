@extends('limonlabs/bigcommerce::layouts.app')

@section('content')
    @include('limonlabs/bigcommerce::layouts.tabs')
    @include('limonlabs/bigcommerce::billing.partials.tabs')

    @include('limonlabs/bigcommerce::layouts.page-title', ['title' => 'Billing History'])

    <div class="bg-white shadow-md p-5 mt-8">
        <table class="border-collapse table-auto w-full text-sm">
            <thead>
                <tr>
                    <th class="border-b border-[#9a9da1] font-medium p-4 pl-8 pt-0 pb-3 text-slate-400 text-left">Date</th>
                    <th class="border-b border-[#9a9da1] font-medium p-4 pl-8 pt-0 pb-3 text-slate-400 text-left"></th>
                </tr>
            </thead>
            <tbody class="bg-white">
                @foreach ($invoices as $invoice)
                    <tr>
                        <td class="border-b border-[#d1d5db] p-4 pl-8 text-slate-500">{{ $invoice->date()->toFormattedDateString() }}</td>
                        <td class="border-b border-[#d1d5db] p-4 pl-8 text-slate-500">
                            @foreach ($invoice->subscriptions() as $subscription)
                                @php
                                    $details = $subscription->toArray();

                                    echo $details['description'];
                                @endphp
                            @endforeach
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
