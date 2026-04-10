@extends('layouts.admin')
@section('title', 'Pembayaran Air')

@section('content')

    {{-- Filter Kompak --}}
    <div class="bg-white rounded-lg shadow p-3 sm:p-4 mb-6 border border-gray-100">
        <form method="GET" action="{{ route('admin.pembayaran.index') }}"
            class="flex flex-col md:flex-row gap-3 md:items-end">

            <div class="w-full md:w-40">
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Bulan</label>
                <select name="bulan"
                    class="w-full border border-gray-300 rounded-md px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    @foreach (range(1, 12) as $b)
                        <option value="{{ $b }}" {{ $bulan == $b ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($b)->isoFormat('MMMM') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="w-full md:w-32">
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Tahun</label>
                <select name="tahun"
                    class="w-full border border-gray-300 rounded-md px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    @foreach (range(date('Y'), 2020) as $t)
                        <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="w-full md:w-48">
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Status</label>
                <select name="status"
                    class="w-full border border-gray-300 rounded-md px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="">Semua Status</option>
                    <option value="belum_lunas" {{ $status === 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
                    <option value="mencicil" {{ $status === 'mencicil' ? 'selected' : '' }}>Mencicil</option>
                    <option value="lunas" {{ $status === 'lunas' ? 'selected' : '' }}>Lunas</option>
                </select>
            </div>

            <div class="w-full md:w-auto mt-2 md:mt-0 md:ml-auto">
                <button type="submit"
                    class="w-full justify-center bg-blue-600 text-white px-5 py-1.5 rounded-md text-xs font-medium hover:bg-blue-700 transition">
                    Filter
                </button>
            </div>

        </form>
    </div>

    {{-- Tabel Cerdas --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 sm:p-6 border-b">
            <h2 class="text-base sm:text-lg font-semibold text-gray-700">
                Tagihan Air — {{ \Carbon\Carbon::create()->month($bulan)->isoFormat('MMMM') }} {{ $tahun }}
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-600 uppercase text-[10px] sm:text-xs tracking-wider border-b">
                    <tr>
                        <th class="px-4 py-3 sm:px-6">No</th>
                        <th class="px-4 py-3 sm:px-6 min-w-[150px]">Pelanggan</th>
                        <th class="hidden sm:table-cell px-6 py-3">Volume</th>
                        <th class="hidden md:table-cell px-6 py-3">Total Tagihan</th>
                        <th class="hidden md:table-cell px-6 py-3">Terbayar</th>
                        <th class="px-4 py-3 sm:px-6">Sisa</th>
                        <th class="px-4 py-3 sm:px-6">Status</th>
                        <th class="px-4 py-3 sm:px-6 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($tagihan as $index => $t)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 sm:px-6 text-gray-500 text-xs sm:text-sm">
                                {{ ($tagihan->currentPage() - 1) * $tagihan->perPage() + $index + 1 }}
                            </td>
                            <td class="px-4 py-3 sm:px-6">
                                <p class="font-medium text-gray-800 text-xs sm:text-sm">{{ $t->pelanggan->nama_pelanggan }}
                                </p>
                                <p class="text-[10px] sm:text-xs text-blue-600">{{ $t->pelanggan->kode_pelanggan }}</p>
                                <p class="block md:hidden mt-0.5 text-[10px] text-gray-500 font-medium">Tagihan: Rp
                                    {{ number_format($t->total_tagihan, 0, ',', '.') }}</p>
                            </td>
                            <td class="hidden sm:table-cell px-6 py-3 whitespace-nowrap">
                                {{ number_format($t->pencatatanMeter->volume_m3, 2) }} m³
                            </td>
                            <td class="hidden md:table-cell px-6 py-3 font-semibold whitespace-nowrap">
                                Rp {{ number_format($t->total_tagihan, 0, ',', '.') }}
                            </td>
                            <td class="hidden md:table-cell px-6 py-3 text-green-600 font-semibold whitespace-nowrap">
                                Rp {{ number_format($t->total_terbayar, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 sm:px-6 text-red-600 font-bold whitespace-nowrap text-xs sm:text-sm">
                                Rp {{ number_format($t->sisa_tagihan, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 sm:px-6">
                                @if ($t->status_bayar === 'lunas')
                                    <span
                                        class="inline-block px-2.5 py-1 bg-green-100 text-green-700 rounded text-[10px] font-bold uppercase tracking-wide">Lunas</span>
                                @elseif($t->status_bayar === 'mencicil')
                                    <span
                                        class="inline-block px-2.5 py-1 bg-yellow-100 text-yellow-700 rounded text-[10px] font-bold uppercase tracking-wide">Mencicil</span>
                                @else
                                    <span
                                        class="inline-block px-2.5 py-1 bg-red-100 text-red-700 rounded text-[10px] font-bold uppercase tracking-wide">Belum</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 sm:px-6 text-center">
                                <a href="{{ route('admin.pembayaran.show', $t) }}"
                                    class="inline-block px-3 py-1.5 {{ $t->status_bayar === 'lunas' ? 'bg-gray-500 hover:bg-gray-600' : 'bg-blue-600 hover:bg-blue-700' }} text-white rounded text-[10px] sm:text-xs font-medium transition">
                                    {{ $t->status_bayar === 'lunas' ? 'Detail' : 'Bayar' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-10 text-center text-gray-400">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <p>Belum ada tagihan di periode ini</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($tagihan->hasPages())
            <div class="p-4 border-t">
                {{ $tagihan->links() }}
            </div>
        @endif
    </div>
@endsection
