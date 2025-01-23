<div class="w-full border-b pb-1 mt-5">
    <a href="{{ url('/' . tenant('id') . '/billing') }}" class="px-3 py-2 {{ (request()->is('*billing')) ? 'border-b-2 border-[#4B71FC] text-black text-sm' : 'text-[#4B71FC] hover:bg-[#f0f3ff] text-sm' }}">Pricing Plans</a>
    <a href="{{ url('/' . tenant('id') . '/billing/history') }}" class="px-3 py-2 {{ request()->is('*billing/history*') ? 'border-b-2 border-[#4B71FC] text-black text-sm' : 'text-[#4B71FC] hover:bg-[#f0f3ff] text-sm' }}">Billing History</a>
</div>
