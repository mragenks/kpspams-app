@extends('layouts.admin')
@section('title', 'Pencatatan Meter')

@section('content')

    {{-- Filter Periode, Pencarian & Action Button --}}
    <div class="bg-white rounded-lg shadow p-3 sm:p-4 mb-6 border border-gray-100">
        <form id="filterForm" method="GET" action="{{ route('pencatat.meter.index') }}"
            class="flex flex-col md:flex-row gap-4 md:items-end">

            {{-- Kolom Pencarian --}}
            <div class="w-full md:w-64">
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Cari Pelanggan</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Kode atau Nama..."
                        class="w-full border border-gray-300 rounded-md pl-8 pr-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-2" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>

            {{-- Filter Dari Periode --}}
            <div class="w-full md:w-40">
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Dari Periode</label>
                <input type="month" name="start_periode" value="{{ $startPeriode }}" max="{{ $endPeriode }}" onchange="this.form.submit()"
                    class="w-full border border-gray-300 rounded-md px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white cursor-pointer">
            </div>

            {{-- Filter Sampai Periode --}}
            <div class="w-full md:w-40">
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Sampai Periode</label>
                <input type="month" name="end_periode" value="{{ $endPeriode }}" min="{{ $startPeriode }}" onchange="this.form.submit()"
                    class="w-full border border-gray-300 rounded-md px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white cursor-pointer">
            </div>

            {{-- Tombol Cari & Tambah --}}
            <div class="flex gap-2 w-full md:w-auto mt-2 md:mt-0 md:ml-auto">
                <button type="submit"
                    class="w-full sm:w-auto px-5 py-1.5 bg-gray-800 text-white rounded-md text-xs font-medium hover:bg-gray-900 transition shadow-sm">
                    Cari
                </button>
                <a href="{{ route('pencatat.meter.create') }}"
                    class="w-full sm:w-auto flex justify-center items-center bg-green-600 text-white px-4 py-1.5 rounded-md text-xs font-medium hover:bg-green-700 transition gap-1.5 shadow-sm whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Catat Baru
                </a>
            </div>
        </form>
    </div>

    @if (session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-3 rounded-lg text-sm mb-6 font-semibold">
            {{ session('success') }}
        </div>
    @endif

    {{-- Daftar Belum Dicatat --}}
    @if ($pelangganBelumDicatat->count() > 0)
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-6">
            <button type="button" onclick="toggleBelumDicatat()" class="w-full flex justify-between items-center text-left focus:outline-none">
                <h3 class="text-sm font-bold text-yellow-800 flex items-center gap-1.5 flex-wrap">
                    <span class="flex items-center gap-1.5">
                        <span>⚠️</span>
                        <span>Belum Dicatat Periode Akhir ({{ \Carbon\Carbon::createFromFormat('Y-m', $endPeriode)->isoFormat('MMMM Y') }})</span>
                    </span>
                    <span class="text-xs bg-yellow-200 text-yellow-800 px-2 py-0.5 rounded-full font-semibold sm:ml-2">
                        {{ $pelangganBelumDicatat->count() }} Pelanggan
                    </span>
                </h3>
                <span id="toggle-icon-belum" class="text-yellow-800 transform transition-transform duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </span>
            </button>
            
            <div id="belum-dicatat-list" class="transition-all duration-300">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 mt-4">
                    @foreach ($pelangganBelumDicatat as $pb)
                        <div class="flex justify-between items-center bg-white p-2.5 rounded-lg border border-yellow-100 shadow-xs hover:shadow-sm transition">
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-gray-800 truncate">{{ $pb->nama_pelanggan }}</p>
                                <p class="text-[10px] text-gray-400 font-mono">{{ $pb->kode_pelanggan }}</p>
                            </div>
                            <a href="{{ route('pencatat.meter.create', ['pelanggan_id' => $pb->id]) }}"
                                class="bg-blue-600 hover:bg-blue-700 text-white text-[10px] font-bold px-2.5 py-1 rounded transition whitespace-nowrap">
                                Catat Meter
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Tabel Pencatatan --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 sm:p-6 border-b flex justify-between items-center">
            <h2 class="text-base sm:text-lg font-semibold text-gray-700">
                Pencatatan — {{ \Carbon\Carbon::createFromFormat('Y-m', $startPeriode)->isoFormat('MMM Y') }} s/d
                {{ \Carbon\Carbon::createFromFormat('Y-m', $endPeriode)->isoFormat('MMM Y') }}
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-600 uppercase text-[10px] sm:text-xs tracking-wider border-b">
                    <tr>
                        <th class="px-4 py-3 sm:px-6">No</th>
                        <th class="px-4 py-3 sm:px-6 min-w-[150px]">Pelanggan & Periode</th>
                        <th class="px-6 py-3 text-center">Meter Awal</th>
                        <th class="px-6 py-3 text-center">Meter Akhir</th>
                        <th class="px-6 py-3 text-center">Vol (m³)</th>
                        <th class="px-4 py-3 sm:px-6 text-right">Tagihan</th>
                        <th class="px-4 py-3 sm:px-6 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($pencatatan as $index => $p)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 sm:px-6 text-gray-500">
                                {{ ($pencatatan->currentPage() - 1) * $pencatatan->perPage() + $index + 1 }}
                            </td>
                            <td class="px-4 py-3 sm:px-6">
                                <p class="font-bold text-gray-800">{{ $p->pelanggan->nama_pelanggan }}</p>
                                <div class="flex gap-2 items-center mt-0.5 text-xs text-gray-400">
                                    <span class="font-mono">{{ $p->pelanggan->kode_pelanggan }}</span>
                                    <span>•</span>
                                    <span class="font-semibold text-blue-600">
                                        {{ \Carbon\Carbon::create(null, $p->bulan_periode, 1)->isoFormat('MMMM') }} {{ $p->tahun_periode }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-center font-mono text-gray-600">
                                {{ number_format($p->meter_awal, 1, ',', '.') }}
                            </td>
                            <td class="px-6 py-3 text-center font-mono text-gray-800 font-bold">
                                {{ number_format($p->meter_akhir, 1, ',', '.') }}
                            </td>
                            <td class="px-6 py-3 text-center font-semibold text-blue-600 font-mono">
                                {{ number_format($p->volume_m3, 1, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 sm:px-6 text-right font-bold text-gray-800 font-mono">
                                @if($p->tagihanAir)
                                    Rp {{ number_format($p->tagihanAir->total_tagihan, 0, ',', '.') }}
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 sm:px-6 text-center">
                                @if($p->tagihanAir)
                                    @if($p->tagihanAir->status_bayar === 'lunas')
                                        <span class="inline-block px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-[10px] font-bold uppercase">Lunas</span>
                                    @elseif($p->tagihanAir->status_bayar === 'mencicil')
                                        <span class="inline-block px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full text-[10px] font-bold uppercase">Cicil</span>
                                    @else
                                        <span class="inline-block px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-[10px] font-bold uppercase">Belum Lunas</span>
                                    @endif
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 sm:py-12 text-center text-gray-400 font-medium">
                                Tidak ada data pencatatan meter pada periode ini
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($pencatatan->hasPages())
            <div class="p-4 border-t">
                {{ $pencatatan->links() }}
            </div>
        @endif
    </div>

    <script>
        function toggleBelumDicatat() {
            const list = document.getElementById('belum-dicatat-list');
            const icon = document.getElementById('toggle-icon-belum');
            if (!list || !icon) return;

            if (list.classList.contains('hidden')) {
                list.classList.remove('hidden');
                icon.classList.remove('rotate-180');
            } else {
                list.classList.add('hidden');
                icon.classList.add('rotate-180');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Collapse list by default on mobile screens (width < 640px)
            if (window.innerWidth < 640) {
                const list = document.getElementById('belum-dicatat-list');
                const icon = document.getElementById('toggle-icon-belum');
                if (list && icon) {
                    list.classList.add('hidden');
                    icon.classList.add('rotate-180');
                }
            }
        });
    </script>

@endsection
