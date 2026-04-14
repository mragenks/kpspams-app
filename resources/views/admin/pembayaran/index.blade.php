@extends('layouts.admin')
@section('title', 'Pembayaran Air')

@section('content')

    {{-- Filter Kompak --}}
    <div class="bg-white rounded-lg shadow p-3 sm:p-4 mb-6 border border-gray-100">
        {{-- Tambahkan ID filterForm --}}
        <form id="filterForm" method="GET" action="{{ route('admin.pembayaran.index') }}"
            class="flex flex-col md:flex-row gap-3 md:items-end">

            {{-- Filter Bulan & Tahun (Input Type Month) --}}
            <div class="w-full md:w-56">
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Periode
                    Tagihan</label>

                {{-- Input utama yang dilihat user --}}
                <input type="month" id="periodeInput" value="{{ $tahun }}-{{ str_pad($bulan, 2, '0', STR_PAD_LEFT) }}"
                    onchange="submitPeriode()"
                    class="w-full border border-gray-300 rounded-md px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white cursor-pointer">

                {{-- Hidden input --}}
                <input type="hidden" name="bulan" id="hiddenBulan" value="{{ $bulan }}">
                <input type="hidden" name="tahun" id="hiddenTahun" value="{{ $tahun }}">
            </div>

            <div class="w-full md:w-48">
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Status</label>
                {{-- Tambahkan auto-submit saat status diubah --}}
                <select name="status" onchange="document.getElementById('filterForm').submit();"
                    class="w-full border border-gray-300 rounded-md px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white cursor-pointer">
                    <option value="">Semua Status</option>
                    <option value="belum_lunas" {{ $status === 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
                    <option value="mencicil" {{ $status === 'mencicil' ? 'selected' : '' }}>Mencicil</option>
                    <option value="lunas" {{ $status === 'lunas' ? 'selected' : '' }}>Lunas</option>
                </select>
            </div>

            {{-- Tombol Filter Dihapus karena sudah auto-submit --}}

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
                                    class="inline-block px-3 py-1.5 {{ $t->status_bayar === 'lunas' ? 'bg-gray-500 hover:bg-gray-600' : 'bg-blue-600 hover:bg-blue-700' }} text-white rounded text-[10px] sm:text-xs font-medium transition shadow-sm">
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

    {{-- Script untuk auto-submit --}}
    <script>
        function submitPeriode() {
            let val = document.getElementById('periodeInput').value;
            if (val) {
                let parts = val.split('-');
                document.getElementById('hiddenTahun').value = parts[0];
                document.getElementById('hiddenBulan').value = parseInt(parts[1], 10);

                document.getElementById('filterForm').submit();
            }
        }
    </script>
@endsection
