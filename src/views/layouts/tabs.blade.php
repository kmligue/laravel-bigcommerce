<div class="w-full border-b pb-1">
    <a href="{{ '/'. $storeHash .'/overview' }}" class="px-3 py-2 {{ (request()->is('*/overview*') || (!request()->is('*/help*'))) ? 'border-b-4 border-[#4B71FC] text-black' : 'text-[#4B71FC] hover:bg-[#f0f3ff]' }}">Overview</a>
    <a href="{{ '/'. $storeHash .'/help' }}" class="px-3 py-2 {{ (request()->is('*/help*')) ? 'border-b-4 border-[#4B71FC] text-black' : 'text-[#4B71FC] hover:bg-[#f0f3ff]' }}">Help</a>
</div>
