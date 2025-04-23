<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="config('app.url') . '/limonadmin/installs'">
{{ config('app.name') }}
</x-mail::header>
</x-slot:header>

**Stores that are in trial period**

<table style="width: 100%; border-collapse: collapse; border: 1px solid #ddd;">
<tr>
<th style="border: 1px solid #ddd; padding: 10px; text-align: left; background-color: #f4f4f4;">Store Hash</th>
<th style="border: 1px solid #ddd; padding: 10px; text-align: left; background-color: #f4f4f4;">Name</th>
<th style="border: 1px solid #ddd; padding: 10px; text-align: left; background-color: #f4f4f4;">Expiration Date</th>
</tr>
@foreach($storesInTrial as $store)
<tr>
<td style="border: 1px solid #ddd; padding: 10px;">{{ str_replace('stores/', '', $store->store_hash) }}</td>
<td style="border: 1px solid #ddd; padding: 10px;">
    <span>{{ $store->name }}</span>
    <br />
    <small style="font-size: 12px;">{{ $store->first_name }} {{ $store->last_name }}</small>
</td>
<td style="border: 1px solid #ddd; padding: 10px;">{{ $store->trial_ends_at->format('M d, Y') }}</td>
</tr>
@endforeach
</table>

<hr style="margin: 30px 0;" />

**Stores that have expired trial in the past 7 days**

<table style="width: 100%; border-collapse: collapse; border: 1px solid #ddd;">
<tr>
<th style="border: 1px solid #ddd; padding: 10px; text-align: left; background-color: #f4f4f4;">Store Hash</th>
<th style="border: 1px solid #ddd; padding: 10px; text-align: left; background-color: #f4f4f4;">Name</th>
<th style="border: 1px solid #ddd; padding: 10px; text-align: left; background-color: #f4f4f4;">Expiration Date</th>
</tr>
@foreach($storesInExpiredTrial as $store)
<tr>
<td style="border: 1px solid #ddd; padding: 10px;">{{ str_replace('stores/', '', $store->store_hash) }}</td>
<td style="border: 1px solid #ddd; padding: 10px;">
    <span>{{ $store->name }}</span>
    <br />
    <small style="font-size: 12px;">{{ $store->first_name }} {{ $store->last_name }}</small>
</td>
<td style="border: 1px solid #ddd; padding: 10px;">{{ $store->trial_ends_at->format('M d, Y') }}</td>
</tr>
@endforeach
</table>

<hr style="margin: 30px 0;" />

**Stores that have uninstalled app in the past 7 days**

<table style="width: 100%; border-collapse: collapse; border: 1px solid #ddd;">
<tr>
<th style="border: 1px solid #ddd; padding: 10px; text-align: left; background-color: #f4f4f4;">Store Hash</th>
<th style="border: 1px solid #ddd; padding: 10px; text-align: left; background-color: #f4f4f4;">Name</th>
<th style="border: 1px solid #ddd; padding: 10px; text-align: left; background-color: #f4f4f4;">Plan</th>
<th style="border: 1px solid #ddd; padding: 10px; text-align: left; background-color: #f4f4f4;">Install Date</th>
</tr>
@foreach($storesUninstalledApps as $store)
<tr>
<td style="border: 1px solid #ddd; padding: 10px;">{{ str_replace('stores/', '', $store->store_hash) }}</td>
<td style="border: 1px solid #ddd; padding: 10px;">
    <span>{{ $store->name }}</span>
    <br />
    <small style="font-size: 12px;">{{ $store->first_name }} {{ $store->last_name }}</small>
</td>
<td style="border: 1px solid #ddd; padding: 10px;">
    @if ($store->plan)
        @foreach ($plans as $key => $plan)
            @if ($store->plan && $store->plan['plan_id'] == $plan['plan_id'])
                {{ ucfirst($key) }}
            @endif
        @endforeach
    @endif
</td>
<td style="border: 1px solid #ddd; padding: 10px;">{{ $store->created_at->format('M d, Y') }}</td>
</tr>
@endforeach
</table>

<hr style="margin: 30px 0;" />

**Stores that are in a standard plan**

<table style="width: 100%; border-collapse: collapse; border: 1px solid #ddd;">
<tr>
<th style="border: 1px solid #ddd; padding: 10px; text-align: left; background-color: #f4f4f4;">Store Hash</th>
<th style="border: 1px solid #ddd; padding: 10px; text-align: left; background-color: #f4f4f4;">Name</th>
<th style="border: 1px solid #ddd; padding: 10px; text-align: left; background-color: #f4f4f4;">Plan</th>
<th style="border: 1px solid #ddd; padding: 10px; text-align: left; background-color: #f4f4f4;">Install Date</th>
</tr>
@foreach($storesInSubscription as $store)
<tr>
<td style="border: 1px solid #ddd; padding: 10px;">{{ str_replace('stores/', '', $store->store_hash) }}</td>
<td style="border: 1px solid #ddd; padding: 10px;">
    <span>{{ $store->name }}</span>
    <br />
    <small style="font-size: 12px;">{{ $store->first_name }} {{ $store->last_name }}</small>
</td>
<td style="border: 1px solid #ddd; padding: 10px;">
    @if ($store->plan)
        @foreach ($plans as $key => $plan)
            @if ($store->plan && $store->plan['plan_id'] == $plan['plan_id'])
                {{ ucfirst($key) }}
            @endif
        @endforeach
    @endif
</td>
<td style="border: 1px solid #ddd; padding: 10px;">{{ $store->created_at->format('M d, Y') }}</td>
</tr>
@endforeach
</table>

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{{ $subcopy }}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
© {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
