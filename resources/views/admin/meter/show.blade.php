@extends('layouts.admin')
@section('title', 'Detail Pencatatan Meter')

@section('content')
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow p-4 sm:p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-base sm:text-lg font-semibold text-gray-700">Detail Pencatatan Meter</h2>

            {{-- WADAH TOMBOL AKSI --}}
            <div class="flex items-center gap-3">
                {{-- TOMBOL EDIT METERAN BARU --}}
                <a href="{{ route('admin.meter.edit', $meter->id) }}"
                    class="bg-yellow-500 hover:bg-yellow-600 text-white font-medium py-1.5 px-3 sm:py-2 sm:px-4 rounded-lg text-xs sm:text-sm flex items-center transition-colors">
                    <svg class="w-4 h-4 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                        </path>
                    </svg>
                    <span class="hidden sm:inline">Edit Meteran</span>
                </a>

                {{-- TOMBOL KEMBALI LAMA --}}
                <a href="{{ route('admin.meter.index') }}"
                    class="text-sm text-blue-600 hover:text-blue-800 transition flex items-center gap-1 bg-blue-50 hover:bg-blue-100 py-1.5 px-3 rounded-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18">
                        </path>
                    </svg>
                    <span class="hidden sm:inline">Kembali</span>
                </a>
            </div>
        </div>

        <div class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <p class="text-[10px] sm:text-xs text-gray-400 uppercase tracking-wide">Pelanggan</p>
                    <p class="font-semibold text-gray-800 text-sm sm:text-base">{{ $meter->pelanggan->nama_pelanggan }}</p>
                    <p class="text-xs text-blue-600 font-mono mt-0.5">{{ $meter->pelanggan->kode_pelanggan }}</p>
                </div>
                <div>
                    <p class="text-[10px] sm:text-xs text-gray-400 uppercase tracking-wide">Periode</p>
                    <p class="font-semibold text-gray-800 text-sm sm:text-base">
                        {{ \Carbon\Carbon::create()->month($meter->bulan_periode)->isoFormat('MMMM') }}
                        {{ $meter->tahun_periode }}
                    </p>
                </div>
            </div>

            <div
                class="grid grid-cols-3 bg-gray-50 border border-gray-100 rounded-lg py-3 mb-6 divide-x divide-gray-200 shadow-sm">
                <div class="text-center px-1">
                    <p class="text-[9px] sm:text-xs text-gray-500 uppercase tracking-wide mb-0.5">Meter Awal</p>
                    <p class="text-xl sm:text-2xl font-bold text-gray-700">{{ number_format($meter->meter_awal, 2) }}</p>
                </div>
                <div class="text-center px-1">
                    <p class="text-[9px] sm:text-xs text-gray-500 uppercase tracking-wide mb-0.5">Meter Akhir</p>
                    <p class="text-xl sm:text-2xl font-bold text-gray-700">{{ number_format($meter->meter_akhir, 2) }}</p>
                </div>
                <div class="text-center px-1">
                    <p class="text-[9px] sm:text-xs text-gray-500 uppercase tracking-wide mb-0.5">Volume</p>
                    <p class="text-xl sm:text-2xl font-bold text-blue-600">{{ number_format($meter->volume_m3, 2) }} <span
                            class="text-sm font-normal">m³</span></p>
                </div>
            </div>

            @if ($meter->tagihanAir)
                <div class="border border-gray-200 rounded-lg p-4 bg-white">
                    <h3 class="text-sm font-bold text-gray-700 mb-3 border-b pb-2">Rincian Tagihan</h3>
                    <div class="space-y-2.5 text-xs sm:text-sm">
                        <div class="flex justify-between items-center group">
                            <span class="text-gray-500 group-hover:text-gray-700 transition">Biaya Beban</span>
                            <span class="font-medium text-gray-800">Rp
                                {{ number_format($meter->tagihanAir->biaya_beban, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center group">
                            <span class="text-gray-500 group-hover:text-gray-700 transition">Biaya Pemakaian</span>
                            <span class="font-medium text-gray-800">Rp
                                {{ number_format($meter->tagihanAir->biaya_pemakaian, 0, ',', '.') }}</span>
                        </div>
                        <div
                            class="flex justify-between items-center font-bold border-t border-dashed border-gray-300 pt-2.5 mt-1">
                            <span class="text-gray-800">Total Tagihan</span>
                            <span class="text-blue-700 text-sm sm:text-base">
                                Rp {{ number_format($meter->tagihanAir->total_tagihan, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center text-green-600 font-medium pt-1">
                            <span>Terbayar</span>
                            <span>Rp {{ number_format($meter->tagihanAir->total_terbayar, 0, ',', '.') }}</span>
                        </div>
                        <div
                            class="flex justify-between items-center text-red-600 font-bold bg-red-50 p-1.5 rounded -mx-1.5 px-1.5">
                            <span>Sisa Tagihan</span>
                            <span>Rp {{ number_format($meter->tagihanAir->sisa_tagihan, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center pt-2 mt-2 border-t border-gray-100">
                            <span class="text-gray-500 font-medium">Status Pembayaran</span>
                            @if ($meter->tagihanAir->status_bayar === 'lunas')
                                <span
                                    class="px-2.5 py-1 bg-green-100 text-green-700 rounded text-[10px] font-bold uppercase tracking-wider">Lunas</span>
                            @elseif($meter->tagihanAir->status_bayar === 'mencicil')
                                <span
                                    class="px-2.5 py-1 bg-yellow-100 text-yellow-700 rounded text-[10px] font-bold uppercase tracking-wider">Mencicil</span>
                            @else
                                <span
                                    class="px-2.5 py-1 bg-red-100 text-red-700 rounded text-[10px] font-bold uppercase tracking-wider">Belum
                                    Lunas</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- Foto Meteran --}}
            @if ($meter->foto_meteran)
                <div class="border border-gray-200 rounded-lg p-4 mt-6">
                    <h3 class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z">
                            </path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Bukti Foto Meteran
                    </h3>
                    <div class="bg-gray-50 p-2 rounded-lg inline-block w-full text-center">
                        {{-- DIUBAH: Mengubah Tag <a> Link Tab Baru Menjadi Button Pemicu Modal Pop-up --}}
                        <button type="button" onclick="bukaModalFoto('{{ asset('storage/' . $meter->foto_meteran) }}')"
                            class="focus:outline-none block w-full max-w-sm mx-auto">
                            <img src="{{ asset('storage/' . $meter->foto_meteran) }}"
                                alt="Foto Meteran {{ $meter->pelanggan->nama_pelanggan }}"
                                class="w-full rounded border border-gray-300 shadow-sm hover:opacity-80 transition duration-200 cursor-pointer object-cover">
                        </button>
                        <p class="text-[10px] sm:text-xs text-gray-400 mt-2 font-medium">Klik foto untuk memperbesar
                            tampilan</p>
                    </div>
                </div>
            @endif

        </div>
    </div>

    {{-- MODAL POP-UP VIEWER FIXED (TRANSPARAN DI BELAKANG & HEADER TERSTRUKTUR RAPI) --}}
    <div id="modalPreviewFoto"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden border flex flex-col animate-fade-in">
            <div class="flex justify-between items-center px-5 py-4 border-b bg-gray-50">
                <h3 class="text-sm font-bold text-gray-800">Pratinjau Foto Meteran</h3>
                <button type="button" onclick="tutupModalFoto()"
                    class="text-gray-400 hover:text-gray-600 transition p-1.5 rounded-lg hover:bg-gray-200/70 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="p-5 flex justify-center bg-gray-100/30">
                <img id="imgPreviewModal" src="" alt="Foto Fisik Meteran"
                    class="max-h-[55vh] max-w-full object-contain rounded-xl shadow-sm border bg-white">
            </div>
        </div>
    </div>

    {{-- Script Logika JavaScript Modal Preview --}}
    <script>
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

        // Tutup otomatis jika klik area transparan di luar kotak putih modal
        modalFoto.addEventListener('click', function(e) {
            if (e.target === this || e.target.id === 'modalPreviewFoto') {
                tutupModalFoto();
            }
        });

        // Tutup menggunakan tombol Escape (ESC) pada keyboard laptop
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                tutupModalFoto();
            }
        });
    </script>
@endsection
