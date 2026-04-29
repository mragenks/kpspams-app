@extends('layouts.admin')
@section('title', 'Pencatatan Meter')

@section('content')

    {{-- Filter Periode, Pencarian & Action Button --}}
    <div class="bg-white rounded-lg shadow p-3 sm:p-4 mb-6 border border-gray-100">
        <form id="filterForm" method="GET" action="{{ route('admin.meter.index') }}"
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
                <input type="month" name="start_periode" value="{{ $startPeriode }}" onchange="this.form.submit()"
                    class="w-full border border-gray-300 rounded-md px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white cursor-pointer">
            </div>

            {{-- Filter Sampai Periode --}}
            <div class="w-full md:w-40">
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Sampai
                    Periode</label>
                <input type="month" name="end_periode" value="{{ $endPeriode }}" onchange="this.form.submit()"
                    class="w-full border border-gray-300 rounded-md px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white cursor-pointer">
            </div>

            {{-- Tombol Cari & Tambah --}}
            <div class="flex gap-2 w-full md:w-auto mt-2 md:mt-0 md:ml-auto">
                <button type="submit"
                    class="w-full sm:w-auto px-5 py-1.5 bg-gray-800 text-white rounded-md text-xs font-medium hover:bg-gray-900 transition shadow-sm">
                    Cari
                </button>
                <a href="{{ route('admin.meter.create') }}"
                    class="w-full sm:w-auto flex justify-center items-center bg-green-600 text-white px-4 py-1.5 rounded-md text-xs font-medium hover:bg-green-700 transition gap-1.5 shadow-sm whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Catat Baru
                </a>
            </div>
        </form>
    </div>

    {{-- Info pelanggan belum dicatat (Dicek pada bulan target akhir) --}}
    @if ($pelangganBelumDicatat->count() > 0)
        <div class="bg-yellow-50 border border-yellow-300 rounded-lg p-3 sm:p-4 mb-6">
            <p class="text-xs sm:text-sm text-yellow-800 font-medium leading-relaxed">
                <span class="inline-block mr-1">⚠️</span>
                <strong>{{ $pelangganBelumDicatat->count() }} pelanggan</strong> belum dicatat pada periode akhir
                ({{ \Carbon\Carbon::createFromFormat('Y-m', $endPeriode)->isoFormat('MMMM Y') }}):
                <span
                    class="text-gray-600 ml-1">{{ Str::limit($pelangganBelumDicatat->pluck('nama_pelanggan')->join(', '), 100) }}</span>
            </p>
        </div>
    @endif

    {{-- Tabel Pencatatan --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 sm:p-6 border-b flex justify-between items-center">
            <h2 class="text-base sm:text-lg font-semibold text-gray-700">
                Pencatatan — {{ \Carbon\Carbon::createFromFormat('Y-m', $startPeriode)->isoFormat('MMM Y') }} s/d
                {{ \Carbon\Carbon::createFromFormat('Y-m', $endPeriode)->isoFormat('MMM Y') }}
            </h2>
            @if (request('search'))
                <span class="text-xs text-gray-500 bg-gray-100 px-3 py-1 rounded-full">
                    Mencari: "{{ request('search') }}"
                </span>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-600 uppercase text-[10px] sm:text-xs tracking-wider border-b">
                    <tr>
                        <th class="px-4 py-3 sm:px-6">No</th>
                        <th class="px-4 py-3 sm:px-6 min-w-[150px]">Pelanggan & Periode</th>
                        <th class="hidden md:table-cell px-6 py-3 text-center">Meter Awal</th>
                        <th class="hidden md:table-cell px-6 py-3 text-center">Meter Akhir</th>
                        <th class="hidden sm:table-cell px-6 py-3 text-center">Vol (m³)</th>
                        <th class="px-4 py-3 sm:px-6 text-right">Tagihan</th>
                        <th class="px-4 py-3 sm:px-6 text-center">Status</th>
                        <th class="px-4 py-3 sm:px-6 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($pencatatan as $index => $p)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 sm:px-6 text-gray-500 text-xs sm:text-sm">
                                {{ ($pencatatan->currentPage() - 1) * $pencatatan->perPage() + $index + 1 }}
                            </td>
                            <td class="px-4 py-3 sm:px-6">
                                <p class="font-medium text-gray-800 text-xs sm:text-sm">{{ $p->pelanggan->nama_pelanggan }}
                                </p>
                                <p class="text-[10px] sm:text-xs text-blue-600 font-mono mb-1">
                                    {{ $p->pelanggan->kode_pelanggan }}</p>
                                {{-- LABEL PERIODE DITAMBAHKAN DI SINI AGAR JELAS --}}
                                <span class="text-[10px] bg-blue-100 text-blue-800 px-2 py-0.5 rounded font-bold">
                                    {{ \Carbon\Carbon::create(null, $p->bulan_periode, 1)->isoFormat('MMM') }}
                                    {{ $p->tahun_periode }}
                                </span>
                                <p class="block sm:hidden mt-1 text-[10px] text-gray-500 font-medium">Vol:
                                    {{ number_format($p->volume_m3, 0) }} m³</p>
                            </td>
                            <td class="hidden md:table-cell px-6 py-3 text-center text-gray-500 font-mono text-xs">
                                {{ number_format($p->meter_awal, 0) }}
                            </td>
                            <td
                                class="hidden md:table-cell px-6 py-3 text-center text-gray-800 font-mono text-xs font-semibold">
                                {{ number_format($p->meter_akhir, 0) }}
                            </td>
                            <td class="hidden sm:table-cell px-6 py-3 text-center font-bold text-blue-600">
                                {{ number_format($p->volume_m3, 0) }}
                            </td>
                            <td
                                class="px-4 py-3 sm:px-6 font-bold text-gray-800 text-right whitespace-nowrap text-xs sm:text-sm">
                                Rp {{ number_format($p->tagihanAir->total_tagihan ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 sm:px-6 text-center">
                                @if ($p->tagihanAir)
                                    @if ($p->tagihanAir->status_bayar === 'lunas')
                                        <span
                                            class="inline-block px-2.5 py-1 bg-green-100 text-green-700 rounded text-[10px] font-bold uppercase tracking-wide">Lunas</span>
                                    @elseif($p->tagihanAir->status_bayar === 'mencicil')
                                        <span
                                            class="inline-block px-2.5 py-1 bg-yellow-100 text-yellow-700 rounded text-[10px] font-bold uppercase tracking-wide">Mencicil</span>
                                    @else
                                        <span
                                            class="inline-block px-2.5 py-1 bg-red-100 text-red-700 rounded text-[10px] font-bold uppercase tracking-wide">Belum</span>
                                    @endif
                                @endif
                            </td>
                            <td class="px-4 py-3 sm:px-6">
                                <div class="flex flex-col sm:flex-row gap-1.5 justify-center">
                                    <a href="{{ route('admin.meter.show', $p) }}"
                                        class="text-center px-2 py-1.5 sm:px-3 sm:py-1.5 bg-blue-600 text-white rounded text-[10px] sm:text-xs font-medium hover:bg-blue-700 transition">
                                        Detail
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-10 text-center text-gray-400">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                    @if (request('search'))
                                        <p>Data pencatatan meter untuk "{{ request('search') }}" tidak ditemukan.</p>
                                    @else
                                        <p>Belum ada pencatatan meter di rentang periode ini</p>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($pencatatan->hasPages())
            <div class="p-4 border-t">
                {{ $pencatatan->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
@endsection
