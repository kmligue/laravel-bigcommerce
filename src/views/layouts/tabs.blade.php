<div class="w-full border-b pb-1">
    <a href="/{{ tenant('id') }}/overview" class="px-3 py-2 {{ (request()->is('*overview*') || (!request()->is('*help*') && !request()->is('*billing*'))) ? 'border-b-4 border-[#4B71FC] text-black' : 'text-[#4B71FC] hover:bg-[#f0f3ff]' }}">Overview</a>
    <a href="/{{ tenant('id') }}/help" class="px-3 py-2 {{ (request()->is('*help*')) ? 'border-b-4 border-[#4B71FC] text-black' : 'text-[#4B71FC] hover:bg-[#f0f3ff]' }}">Help</a>
    <a href="/{{ tenant('id') }}/billing" class="px-3 py-2 {{ request()->is('*billing*') ? 'border-b-4 border-[#4B71FC] text-black' : 'text-[#4B71FC] hover:bg-[#f0f3ff]' }}">Billing</a>
</div>
