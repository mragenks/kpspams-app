@extends('layouts.admin')
@section('title', 'Arus Kas')

@section('content')

    {{-- Summary Cards (1 Baris isi 3 - Mobile Optimized) --}}
    <div class="grid grid-cols-3 gap-2 sm:gap-4 mb-6">

        <div
            class="bg-white rounded-lg shadow p-2 sm:p-4 flex flex-col md:flex-row items-center justify-center md:justify-start text-center md:text-left overflow-hidden">
            <div class="p-1.5 sm:p-2.5 rounded-full bg-green-100 text-green-600 mb-1.5 md:mb-0 md:mr-3 shrink-0">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" />
                </svg>
            </div>
            <div class="min-w-0 w-full">
                <p class="text-[9px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wide md:mb-0.5 truncate">
                    <span class="hidden sm:inline">Total </span>Masuk
                </p>
                <p class="text-xs sm:text-lg font-bold text-green-600 truncate"
                    title="Rp {{ number_format($totalMasuk, 0, ',', '.') }}">
                    <span class="hidden sm:inline">Rp </span>{{ number_format($totalMasuk, 0, ',', '.') }}
                </p>
            </div>
        </div>

        <div
            class="bg-white rounded-lg shadow p-2 sm:p-4 flex flex-col md:flex-row items-center justify-center md:justify-start text-center md:text-left overflow-hidden">
            <div class="p-1.5 sm:p-2.5 rounded-full bg-red-100 text-red-600 mb-1.5 md:mb-0 md:mr-3 shrink-0">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6" />
                </svg>
            </div>
            <div class="min-w-0 w-full">
                <p class="text-[9px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wide md:mb-0.5 truncate">
                    <span class="hidden sm:inline">Total </span>Keluar
                </p>
                <p class="text-xs sm:text-lg font-bold text-red-600 truncate"
                    title="Rp {{ number_format($totalKeluar, 0, ',', '.') }}">
                    <span class="hidden sm:inline">Rp </span>{{ number_format($totalKeluar, 0, ',', '.') }}
                </p>
            </div>
        </div>

        <div
            class="bg-white rounded-lg shadow p-2 sm:p-4 flex flex-col md:flex-row items-center justify-center md:justify-start text-center md:text-left overflow-hidden">
            <div class="p-1.5 sm:p-2.5 rounded-full bg-blue-100 text-blue-600 mb-1.5 md:mb-0 md:mr-3 shrink-0">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
            </div>
            <div class="min-w-0 w-full">
                <p class="text-[9px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wide md:mb-0.5 truncate">
                    Selisih<span class="hidden sm:inline"> Bulan Ini</span>
                </p>
                <p class="text-xs sm:text-lg font-bold {{ $totalMasuk - $totalKeluar >= 0 ? 'text-blue-600' : 'text-red-600' }} truncate"
                    title="Rp {{ number_format($totalMasuk - $totalKeluar, 0, ',', '.') }}">
                    <span class="hidden sm:inline">Rp </span>{{ number_format($totalMasuk - $totalKeluar, 0, ',', '.') }}
                </p>
            </div>
        </div>

    </div>

    {{-- Filter & Action Buttons (Utility Bar Kompak) --}}
    <div class="bg-white rounded-lg shadow p-3 mb-6 border border-gray-100">
        {{-- Tambahkan ID filterForm --}}
        <form id="filterForm" method="GET" action="{{ route('admin.arus-kas.index') }}"
            class="flex flex-col md:flex-row gap-3 md:items-end">

            {{-- Filter Bulan & Tahun (Input Type Month) --}}
            <div class="w-full md:w-56">
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Periode
                    Transaksi</label>

                {{-- Input utama yang dilihat user --}}
                <input type="month" id="periodeInput"
                    value="{{ $tahun }}-{{ str_pad($bulan, 2, '0', STR_PAD_LEFT) }}" onchange="submitPeriode()"
                    class="w-full border border-gray-300 rounded-md px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white cursor-pointer">

                {{-- Hidden input --}}
                <input type="hidden" name="bulan" id="hiddenBulan" value="{{ $bulan }}">
                <input type="hidden" name="tahun" id="hiddenTahun" value="{{ $tahun }}">
            </div>

            <div class="w-full md:w-48">
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Akun Kas</label>
                {{-- Tambahkan auto-submit saat Akun Kas diubah --}}
                <select name="akun_kas_id" onchange="document.getElementById('filterForm').submit();"
                    class="w-full border border-gray-300 rounded-md px-2.5 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white cursor-pointer">
                    <option value="">Semua Akun</option>
                    @foreach ($akunKasList as $ak)
                        <option value="{{ $ak->id }}" {{ $akunKasId == $ak->id ? 'selected' : '' }}>
                            {{ $ak->nama_akun }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="w-full md:w-48">
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Jenis Transaksi</label>
                <select name="jenis" onchange="document.getElementById('filterForm').submit();"
                    class="w-full border border-gray-300 rounded-md px-2.5 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white cursor-pointer">
                    <option value="">Semua Transaksi</option>
                    <option value="masuk" {{ isset($jenis) && $jenis == 'masuk' ? 'selected' : '' }}>Pemasukan</option>
                    <option value="keluar" {{ isset($jenis) && $jenis == 'keluar' ? 'selected' : '' }}>Pengeluaran</option>
                </select>
            </div>

            <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto mt-2 md:mt-0 md:ml-auto">
                {{-- Tombol Filter dihapus karena sudah digantikan oleh auto-submit --}}
                <a href="{{ route('admin.arus-kas.create') }}?jenis=masuk"
                    class="flex-1 md:flex-none flex justify-center items-center bg-green-600 text-white px-3 py-1.5 rounded-md text-xs font-medium hover:bg-green-700 transition gap-1 shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Pemasukan
                </a>
                <a href="{{ route('admin.arus-kas.create') }}?jenis=keluar"
                    class="flex-1 md:flex-none flex justify-center items-center bg-red-600 text-white px-3 py-1.5 rounded-md text-xs font-medium hover:bg-red-700 transition gap-1 shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Pengeluaran
                </a>
            </div>
        </form>
    </div>

    {{-- Tabel Buku Kas --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 sm:p-6 border-b">
            <h2 class="text-lg font-semibold text-gray-700">
                Buku Kas — {{ \Carbon\Carbon::create()->month($bulan)->isoFormat('MMMM') }} {{ $tahun }}
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-600 uppercase text-[10px] sm:text-xs tracking-wider border-b">
                    <tr>
                        <th class="px-4 py-3 sm:px-6">No</th>
                        <th class="px-4 py-3 sm:px-6 whitespace-nowrap">Tanggal</th>
                        <th class="px-4 py-3 sm:px-6 min-w-[150px]">Keterangan</th>
                        <th class="hidden md:table-cell px-6 py-3">Akun Kas</th>
                        <th class="px-4 py-3 sm:px-6 text-right">Masuk</th>
                        <th class="px-4 py-3 sm:px-6 text-right">Keluar</th>
                        <th class="hidden lg:table-cell px-6 py-3">No. Bukti</th>
                        <th class="px-4 py-3 sm:px-6 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($arusKas as $index => $ak)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 sm:px-6 text-gray-500">
                                {{ ($arusKas->currentPage() - 1) * $arusKas->perPage() + $index + 1 }}
                            </td>
                            <td class="px-4 py-3 sm:px-6 text-gray-700 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($ak->tanggal)->isoFormat('D MMM Y') }}
                            </td>
                            <td class="px-4 py-3 sm:px-6 text-gray-800 text-xs sm:text-sm">
                                {{ $ak->keterangan }}
                                <div class="block md:hidden mt-1 text-[10px] text-gray-400">
                                    {{ $ak->akunKas->nama_akun }}
                                </div>
                            </td>
                            <td class="hidden md:table-cell px-6 py-3 text-gray-600 text-xs">
                                {{ $ak->akunKas->nama_akun }}
                            </td>

                            <td class="px-4 py-3 sm:px-6 text-right font-medium text-green-600 whitespace-nowrap">
                                @if (in_array($ak->jenis_transaksi, ['masuk', 'mutasi_masuk']))
                                    {{ number_format($ak->nominal, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>

                            <td class="px-4 py-3 sm:px-6 text-right font-medium text-red-600 whitespace-nowrap">
                                @if (in_array($ak->jenis_transaksi, ['keluar', 'mutasi_keluar']))
                                    {{ number_format($ak->nominal, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>

                            <td class="hidden lg:table-cell px-6 py-3 text-gray-500 text-xs">
                                {{ $ak->no_bukti ?? '-' }}
                            </td>
                            <td class="px-4 py-3 sm:px-6 text-center">
                                @if (!$ak->pembayaran_id)
                                    <span
                                        class="px-2.5 py-1 bg-blue-100 text-blue-700 rounded text-[10px] font-medium tracking-wide">Manual</span>
                                @else
                                    <span
                                        class="px-2.5 py-1 bg-gray-100 text-gray-500 rounded text-[10px] font-medium tracking-wide">Sistem</span>
                                @endif
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
                                    <p>Belum ada transaksi di periode ini</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($arusKas->hasPages())
            <div class="p-4 border-t">
                {{ $arusKas->links() }}
            </div>
        @endif
    </div>

    {{-- Script untuk auto-submit dari tipe input bulan --}}
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
