{{-- Dashboard --}}
<a href="{{ route('ketua.dashboard') }}"
    class="flex items-center px-4 py-2 mx-2 rounded-lg text-sm {{ request()->routeIs('ketua.dashboard') ? 'bg-blue-700 text-white' : 'text-blue-100 hover:bg-blue-700' }} transition mb-1">
    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
    </svg>
    Dashboard
</a>

{{-- Laporan --}}
<div class="px-4 py-1 mt-3">
    <p class="text-xs text-blue-400 uppercase font-semibold tracking-wider">Laporan</p>
</div>
<a href="{{ route('ketua.laporan.index') }}"
    class="flex items-center px-4 py-2 mx-2 rounded-lg text-sm {{ request()->routeIs('ketua.laporan.*') ? 'bg-blue-700 text-white' : 'text-blue-100 hover:bg-blue-700' }} transition mb-1">
    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
    </svg>
    Laporan
</a>

{{-- Buku Kas --}}
<a href="{{ route('ketua.arus-kas.index') }}"
    class="flex items-center px-4 py-2 mx-2 rounded-lg text-sm {{ request()->routeIs('ketua.arus-kas.*') ? 'bg-blue-700 text-white' : 'text-blue-100 hover:bg-blue-700' }} transition mb-1">
    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
    Buku Kas
</a>
