<x-mail::message>
**Stores that are in trial period**

<table style="width: 100%; border-collapse: collapse; border: 1px solid #ddd;">
<tr>
<th style="border: 1px solid #ddd; padding: 10px; text-align: left; background-color: #f4f4f4;">Store Hash</th>
<th style="border: 1px solid #ddd; padding: 10px; text-align: left; background-color: #f4f4f4;">Store Name</th>
<th style="border: 1px solid #ddd; padding: 10px; text-align: left; background-color: #f4f4f4;">Expiration Date</th>
</tr>
@foreach($storesInTrial as $store)
<tr>
<td style="border: 1px solid #ddd; padding: 10px;">{{ str_replace('stores/', '', $store->store_hash) }}</td>
<td style="border: 1px solid #ddd; padding: 10px;">{{ $store->name }}</td>
<td style="border: 1px solid #ddd; padding: 10px;">{{ $store->trial_ends_at->format('M d, Y') }}</td>
</tr>
@endforeach
</table>

<hr style="margin: 30px 0;" />

**Stores that have expired trial in the past 7 days**

<table style="width: 100%; border-collapse: collapse; border: 1px solid #ddd;">
<tr>
<th style="border: 1px solid #ddd; padding: 10px; text-align: left; background-color: #f4f4f4;">Store Hash</th>
<th style="border: 1px solid #ddd; padding: 10px; text-align: left; background-color: #f4f4f4;">Store Name</th>
<th style="border: 1px solid #ddd; padding: 10px; text-align: left; background-color: #f4f4f4;">Expiration Date</th>
</tr>
@foreach($storesInExpiredTrial as $store)
<tr>
<td style="border: 1px solid #ddd; padding: 10px;">{{ str_replace('stores/', '', $store->store_hash) }}</td>
<td style="border: 1px solid #ddd; padding: 10px;">{{ $store->name }}</td>
<td style="border: 1px solid #ddd; padding: 10px;">{{ $store->trial_ends_at->format('M d, Y') }}</td>
</tr>
@endforeach
</table>

<hr style="margin: 30px 0;" />

**Stores that have uninstalled app in the past 7 days**

<table style="width: 100%; border-collapse: collapse; border: 1px solid #ddd;">
<tr>
<th style="border: 1px solid #ddd; padding: 10px; text-align: left; background-color: #f4f4f4;">Store Hash</th>
<th style="border: 1px solid #ddd; padding: 10px; text-align: left; background-color: #f4f4f4;">Store Name</th>
<th style="border: 1px solid #ddd; padding: 10px; text-align: left; background-color: #f4f4f4;">Plan</th>
<th style="border: 1px solid #ddd; padding: 10px; text-align: left; background-color: #f4f4f4;">Expiration Date</th>
</tr>
@foreach($storesUninstalledApps as $store)
<tr>
<td style="border: 1px solid #ddd; padding: 10px;">{{ str_replace('stores/', '', $store->store_hash) }}</td>
<td style="border: 1px solid #ddd; padding: 10px;">{{ $store->name }}</td>
<td style="border: 1px solid #ddd; padding: 10px;">
    @if ($store->plan)
        @foreach ($plans as $key => $plan)
            @if ($store->plan && $store->plan['plan_id'] == $plan['plan_id'])
                {{ ucfirst($key) }}
            @endif
        @endforeach
    @endif
</td>
<td style="border: 1px solid #ddd; padding: 10px;">{{ $store->trial_ends_at->format('M d, Y') }}</td>
</tr>
@endforeach
</table>

<hr style="margin: 30px 0;" />

**Stores that are in a standard plan**

<table style="width: 100%; border-collapse: collapse; border: 1px solid #ddd;">
<tr>
<th style="border: 1px solid #ddd; padding: 10px; text-align: left; background-color: #f4f4f4;">Store Hash</th>
<th style="border: 1px solid #ddd; padding: 10px; text-align: left; background-color: #f4f4f4;">Store Name</th>
<th style="border: 1px solid #ddd; padding: 10px; text-align: left; background-color: #f4f4f4;">Plan</th>
<th style="border: 1px solid #ddd; padding: 10px; text-align: left; background-color: #f4f4f4;">Expiration Date</th>
</tr>
@foreach($storesInSubscription as $store)
<tr>
<td style="border: 1px solid #ddd; padding: 10px;">{{ str_replace('stores/', '', $store->store_hash) }}</td>
<td style="border: 1px solid #ddd; padding: 10px;">{{ $store->name }}</td>
<td style="border: 1px solid #ddd; padding: 10px;">
    @if ($store->plan)
        @foreach ($plans as $key => $plan)
            @if ($store->plan && $store->plan['plan_id'] == $plan['plan_id'])
                {{ ucfirst($key) }}
            @endif
        @endforeach
    @endif
</td>
<td style="border: 1px solid #ddd; padding: 10px;">{{ $store->trial_ends_at->format('M d, Y') }}</td>
</tr>
@endforeach
</table>
</x-mail::message>
