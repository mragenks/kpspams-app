@extends('layouts.admin')
@section('title', 'Buku Kas')

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

    {{-- Filter (Utility Bar Kompak - Tanpa Action Buttons) --}}
    <div class="bg-white rounded-lg shadow p-3 mb-6 border border-gray-100">
        <form id="filterForm" method="GET" action="{{ route('ketua.arus-kas.index') }}"
            class="flex flex-col md:flex-row gap-3 md:items-end">

            {{-- Filter Bulan & Tahun (Input Type Month) --}}
            <div class="w-full md:flex-1">
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Periode
                    Transaksi</label>
                <input type="month" id="periodeInput"
                    value="{{ $tahun }}-{{ str_pad($bulan, 2, '0', STR_PAD_LEFT) }}" onchange="submitPeriode()"
                    class="w-full border border-gray-300 rounded-md px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white cursor-pointer">

                <input type="hidden" name="bulan" id="hiddenBulan" value="{{ $bulan }}">
                <input type="hidden" name="tahun" id="hiddenTahun" value="{{ $tahun }}">
            </div>

            <div class="w-full md:flex-1">
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Akun Kas</label>
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

            <div class="w-full md:flex-1">
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Jenis
                    Transaksi</label>
                <select name="jenis" onchange="document.getElementById('filterForm').submit();"
                    class="w-full border border-gray-300 rounded-md px-2.5 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white cursor-pointer">
                    <option value="">Semua Transaksi</option>
                    <option value="masuk" {{ isset($jenis) && $jenis == 'masuk' ? 'selected' : '' }}>Pemasukan</option>
                    <option value="keluar" {{ isset($jenis) && $jenis == 'keluar' ? 'selected' : '' }}>Pengeluaran</option>
                </select>
            </div>

        </form>
    </div>

    {{-- Tabel Buku Kas --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 sm:p-6 border-b flex justify-between items-center">
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
                        <th class="hidden md:table-cell px-6 py-3 whitespace-nowrap">No. Bukti</th>
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
                                <div>{{ \Carbon\Carbon::parse($ak->tanggal)->isoFormat('D MMM Y') }}</div>
                                <div class="text-xs text-gray-500 mt-0.5">Pukul: {{ \Carbon\Carbon::parse($ak->created_at)->format('H:i') }}</div>
                            </td>
                            <td class="px-4 py-3 sm:px-6 text-gray-800 text-xs sm:text-sm">
                                @if (strpos($ak->keterangan, ' - ') !== false)
                                    @php
                                        $parts = explode(' - ', $ak->keterangan, 2);
                                    @endphp
                                    <div>{{ $parts[0] }}</div>
                                    <div class="text-[11px] text-gray-500 mt-0.5"><span class="font-semibold text-gray-600">Keterangan:</span> {{ $parts[1] }}</div>
                                @elseif (strpos($ak->keterangan, 'Deposit/Titipan Saldo: ') === 0 && strpos($ak->keterangan, '. ') !== false)
                                    @php
                                        $parts = explode('. ', $ak->keterangan, 2);
                                    @endphp
                                    <div>{{ $parts[0] }}</div>
                                    @if(isset($parts[1]) && trim($parts[1]) !== '')
                                        <div class="text-[11px] text-gray-500 mt-0.5"><span class="font-semibold text-gray-600">Keterangan:</span> {{ $parts[1] }}</div>
                                    @endif
                                @else
                                    <div>{{ $ak->keterangan }}</div>
                                @endif
                                <div class="mt-1 flex flex-wrap gap-1.5 items-center">
                                    @if ($ak->no_bukti)
                                        <span class="inline-flex md:hidden items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-800 border border-gray-200">
                                            No. Bukti: {{ $ak->no_bukti }}
                                        </span>
                                    @endif
                                    @if ($ak->foto_bukti)
                                        <button type="button" onclick="bukaModalFoto('{{ asset('storage/' . $ak->foto_bukti) }}')"
                                            class="inline-flex items-center text-[10px] font-medium text-blue-600 hover:text-blue-800 hover:underline gap-0.5 focus:outline-none">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            Lihat Foto Bukti
                                        </button>
                                    @endif
                                </div>
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

                            <td class="hidden md:table-cell px-6 py-3 text-gray-500 text-xs font-mono whitespace-nowrap">
                                <div>{{ $ak->no_bukti ?? '-' }}</div>
                                @if ($ak->foto_bukti)
                                    <div class="mt-0.5">
                                        <button type="button" onclick="bukaModalFoto('{{ asset('storage/' . $ak->foto_bukti) }}')"
                                            class="inline-flex items-center text-[10px] font-medium text-blue-600 hover:text-blue-800 hover:underline gap-0.5 focus:outline-none">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            Lihat Foto
                                        </button>
                                    </div>
                                @endif
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
                {{ $arusKas->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

    <div id="modalPreviewFoto"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden border flex flex-col animate-fade-in">
            <div class="flex justify-between items-center px-5 py-4 border-b bg-gray-50">
                <h3 class="text-sm font-bold text-gray-800">Pratinjau Foto Bukti</h3>
                <button type="button" onclick="tutupModalFoto()"
                    class="text-gray-400 hover:text-gray-600 transition p-1.5 rounded-lg hover:bg-gray-200/70 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="p-5 flex justify-center bg-gray-100/30">
                <img id="imgPreviewModal" src="" alt="Foto Bukti"
                    class="max-h-[55vh] max-w-full object-contain rounded-xl shadow-sm border bg-white">
            </div>
        </div>
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

        const modalFoto = document.getElementById('modalPreviewFoto');
        const imgPreviewModal = document.getElementById('imgPreviewModal');

        function bukaModalFoto(url) {
            imgPreviewModal.src = url;
            modalFoto.classList.remove('hidden');
            modalFoto.classList.add('flex');
        }

        function tutupModalFoto() {
            modalFoto.classList.add('hidden');
            modalFoto.classList.remove('flex');
            imgPreviewModal.src = '';
        }

        modalFoto.addEventListener('click', function(e) {
            if (e.target === this || e.target.id === 'modalPreviewFoto') {
                tutupModalFoto();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                tutupModalFoto();
            }
        });
    </script>
@endsection
