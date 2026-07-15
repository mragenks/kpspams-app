{{-- Dashboard --}}
<a href="{{ route('pencatat.dashboard') }}"
    class="flex items-center px-4 py-2 mx-2 rounded-lg text-sm {{ request()->routeIs('pencatat.dashboard') ? 'bg-blue-700 text-white' : 'text-blue-100 hover:bg-blue-700' }} transition mb-1">
    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
    </svg>
    Dashboard
</a>

{{-- Tagihan --}}
<div class="px-4 py-1 mt-3">
    <p class="text-xs text-blue-400 uppercase font-semibold tracking-wider">Tagihan Air</p>
</div>
<a href="{{ route('pencatat.meter.index') }}"
    class="flex items-center px-4 py-2 mx-2 rounded-lg text-sm {{ request()->routeIs('pencatat.meter.*') ? 'bg-blue-700 text-white' : 'text-blue-100 hover:bg-blue-700' }} transition mb-1">
    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
    </svg>
    Pencatatan Meter
</a>
