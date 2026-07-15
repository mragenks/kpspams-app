@extends('layouts.pelanggan')
@section('title', 'Dashboard')

@section('content')

    {{-- Greeting --}}
    <div class="bg-blue-900 text-white rounded-2xl p-6 mb-6 shadow">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-blue-200 text-sm mb-1">Selamat datang,</p>
                <h1 class="text-2xl font-bold">{{ $pelanggan->nama_pelanggan }}</h1>
                <p class="text-blue-300 text-sm mt-1">
                    {{ $pelanggan->kode_pelanggan }} •
                    {{ $pelanggan->alamat ?? 'Desa Singodutan' }}
                </p>
            </div>
            <div class="text-right">
                <span
                    class="px-3 py-1 {{ $pelanggan->status === 'aktif' ? 'bg-green-500' : 'bg-red-500' }} rounded-full text-xs font-medium">
                    {{ ucfirst($pelanggan->status) }}
                </span>
            </div>
        </div>
    </div>

    {{-- Tagihan Hack --}}
    @if ($tagihanAktif)
        <div
            class="bg-white rounded-xl shadow p-6 mb-6 border-l-4 {{ $tagihanAktif->status_bayar === 'mencicil' ? 'border-yellow-500' : 'border-red-500' }}">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-sm text-gray-500">Tagihan Aktif</p>
                    <h2 class="text-lg font-bold text-gray-800">
                        {{ \Carbon\Carbon::create()->month($tagihanAktif->bulan_periode)->isoFormat('MMMM') }}
                        {{ $tagihanAktif->tahun_periode }}
                    </h2>
                </div>
                @if ($tagihanAktif->status_bayar === 'mencicil')
                    <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm font-medium">Mencicil</span>
                @else
                    <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm font-medium">Belum Lunas</span>
                @endif
            </div>

            <div class="grid grid-cols-3 gap-4 mb-4">
                <div class="bg-gray-50 rounded-lg p-3 text-center">
                    <p class="text-xs text-gray-400 uppercase mb-1">Total Tagihan</p>
                    <p class="font-bold text-gray-800">
                        Rp {{ number_format($tagihanAktif->total_tagihan, 0, ',', '.') }}
                    </p>
                </div>
                <div class="bg-green-50 rounded-lg p-3 text-center">
                    <p class="text-xs text-gray-400 uppercase mb-1">Sudah Dibayar</p>
                    <p class="font-bold text-green-600">
                        Rp {{ number_format($tagihanAktif->total_terbayar, 0, ',', '.') }}
                    </p>
                </div>
                <div class="bg-red-50 rounded-lg p-3 text-center">
                    <p class="text-xs text-gray-400 uppercase mb-1">Sisa Tagihan</p>
                    <p class="font-bold text-red-600">
                        Rp {{ number_format($tagihanAktif->sisa_tagihan, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            @if ($tagihanAktif->pencatatanMeter)
                <div class="bg-blue-50 rounded-lg p-3">
                    <span class="text-sm text-blue-700">
                        💧 Pemakaian:
                        <strong>{{ number_format($tagihanAktif->pencatatanMeter->volume_m3, 2) }} m³</strong>
                        ({{ number_format($tagihanAktif->pencatatanMeter->meter_awal, 2) }} →
                        {{ number_format($tagihanAktif->pencatatanMeter->meter_akhir, 2) }})
                    </span>
                </div>
            @endif

            {{-- TOMBOL LIHAT SURAT TAGIHAN --}}
            <div class="mt-3">
                <a href="{{ route('pelanggan.tagihan.surat', $tagihanAktif->id) }}"
                    class="w-full flex items-center justify-center gap-2 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 py-2.5 rounded-lg text-sm font-semibold transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    Lihat & Cetak Detail Tagihan
                </a>
            </div>

            {{-- Form Submit Bukti --}}
            <div class="mt-4 border-t pt-4">
                <p class="text-sm font-semibold text-gray-700 mb-3">📤 Upload Bukti Pembayaran</p>

                @php
                    $pembayaranPending = $tagihanAktif->pembayaran->where('status_validasi', 'pending')->count() > 0;
                    $pembayaranDitolak = $tagihanAktif->pembayaran->where('status_validasi', 'ditolak')->count() > 0;
                @endphp

                @if ($pembayaranPending)
                    <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <p class="text-sm text-blue-700">
                            ✅ Bukti pembayaran sudah dikirim. Menunggu konfirmasi Admin.
                        </p>
                    </div>
                @else
                    @if ($pembayaranDitolak)
                        <div class="mb-3 p-3 bg-red-50 border border-red-200 rounded-lg flex items-start gap-2">
                            <svg class="w-5 h-5 text-red-500 mt-0.5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-sm text-red-700">
                                <strong>Bukti ditolak!</strong> Bukti sebelumnya ditolak oleh Admin. Silakan periksa dan
                                upload ulang bukti yang benar.
                            </p>
                        </div>
                    @endif
                    <button onclick="bukaModalUploadBukti()"
                        class="w-full bg-blue-600 text-white py-2.5 rounded-lg text-sm font-medium hover:bg-blue-700 transition flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        Upload Bukti Pembayaran
                    </button>
                @endif
            </div>
        </div>
    @else
        <div class="bg-white rounded-xl shadow p-6 mb-6 border-l-4 border-green-500">
            <div class="flex items-center gap-3">
                <div class="p-3 bg-green-100 rounded-full">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div>
                    <h2 class="font-semibold text-gray-800">Semua tagihan lunas! ✅</h2>
                    <p class="text-sm text-gray-500">Tidak ada tagihan yang perlu dibayar saat ini.</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Riwayat Tagihan — Hanya 3 Terbaru --}}
    <div class="bg-white rounded-xl shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold text-gray-700">Riwayat Tagihan</h2>
            @if ($riwayatTagihan->total() > 0)
                <button onclick="bukaModalRiwayat()" class="text-sm text-blue-600 hover:underline font-medium">
                    Lihat semua ({{ $riwayatTagihan->total() }}) →
                </button>
            @endif
        </div>

        @if ($riwayatTagihan->count() > 0)
            <div class="space-y-3">
                {{-- Hanya tampilkan 3 terbaru --}}
                @foreach ($riwayatTagihan->take(3) as $tagihan)
                    <div class="border rounded-lg p-4 hover:bg-gray-50 transition">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-semibold text-gray-800">
                                    {{ \Carbon\Carbon::create()->month($tagihan->bulan_periode)->isoFormat('MMMM') }}
                                    {{ $tagihan->tahun_periode }}
                                </p>
                                <div class="flex flex-wrap items-center gap-2 mt-0.5">
                                    @if ($tagihan->pencatatanMeter)
                                        <p class="text-sm text-gray-500">
                                            Pemakaian: {{ number_format($tagihan->pencatatanMeter->volume_m3, 2) }} m³
                                        </p>
                                    @endif
                                    <span class="text-gray-300">•</span>
                                    <a href="{{ route('pelanggan.tagihan.surat', $tagihan->id) }}"
                                        class="text-blue-600 hover:text-blue-800 text-xs font-semibold flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                            </path>
                                        </svg>
                                        Surat Tagihan
                                    </a>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-gray-800">
                                    Rp {{ number_format($tagihan->total_tagihan, 0, ',', '.') }}
                                </p>
                                @if ($tagihan->status_bayar === 'lunas')
                                    <span
                                        class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-medium">Lunas</span>
                                @elseif($tagihan->status_bayar === 'mencicil')
                                    <span
                                        class="px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">Mencicil</span>
                                @else
                                    <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-xs font-medium">Belum
                                        Lunas</span>
                                @endif
                            </div>
                        </div>

                        {{-- Progress bar --}}
                        @if ($tagihan->total_tagihan > 0)
                            <div class="mt-3">
                                <div class="flex justify-between text-xs text-gray-400 mb-1">
                                    <span>Progress Pembayaran</span>
                                    <span>{{ number_format(($tagihan->total_terbayar / $tagihan->total_tagihan) * 100, 0) }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="h-2 rounded-full {{ $tagihan->status_bayar === 'lunas' ? 'bg-green-500' : 'bg-blue-500' }}"
                                        style="width: {{ ($tagihan->total_terbayar / $tagihan->total_tagihan) * 100 }}%">
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Detail Biaya --}}
                        <div class="mt-3 grid grid-cols-3 gap-2 text-xs text-gray-500">
                            <div>
                                <span class="block text-gray-400">Biaya Beban</span>
                                <span>Rp {{ number_format($tagihan->biaya_beban, 0, ',', '.') }}</span>
                            </div>
                            <div>
                                <span class="block text-gray-400">Biaya Pemakaian</span>
                                <span>Rp {{ number_format($tagihan->biaya_pemakaian, 0, ',', '.') }}</span>
                            </div>
                            <div>
                                <span class="block text-gray-400">Sisa Tagihan</span>
                                <span
                                    class="{{ $tagihan->sisa_tagihan > 0 ? 'text-red-600 font-semibold' : 'text-green-600' }}">
                                    Rp {{ number_format($tagihan->sisa_tagihan, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        {{-- Bukti Pembayaran --}}
                        @if ($tagihan->pembayaran->count() > 0)
                            <div class="mt-3 border-t pt-3">
                                <p class="text-xs font-semibold text-gray-500 mb-2">Bukti Pembayaran:</p>
                                <div class="space-y-2">
                                    @foreach ($tagihan->pembayaran as $p)
                                        <div class="flex items-center justify-between bg-gray-50 rounded-lg p-2">
                                            <div class="flex items-center gap-3">
                                                @if ($p->foto_bukti)
                                                    {{-- INSTANCE 1: Diubah dari Tag <a> menjadi Button Pop-up --}}
                                                    <button type="button"
                                                        onclick="bukaModalFoto('{{ asset('storage/' . $p->foto_bukti) }}')"
                                                        class="focus:outline-none">
                                                        <img src="{{ asset('storage/' . $p->foto_bukti) }}"
                                                            alt="Bukti"
                                                            class="w-12 h-12 object-cover rounded-lg border hover:opacity-80 transition cursor-pointer">
                                                    </button>
                                                @else
                                                    <div
                                                        class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
                                                        <svg class="w-5 h-5 text-gray-400" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                    </div>
                                                @endif
                                                <div>
                                                     <p class="text-xs font-medium text-gray-700 flex items-center gap-1.5 flex-wrap">
                                                         <span>Rp {{ number_format($p->nominal_bayar, 0, ',', '.') }}</span>
                                                         @if ($p->arusKas?->no_bukti)
                                                             <span class="text-[9px] font-mono text-gray-500 bg-gray-100 px-1 py-0.5 rounded border border-gray-200">
                                                                 {{ $p->arusKas->no_bukti }}
                                                             </span>
                                                         @endif
                                                     </p>
                                                    <p class="text-xs text-gray-400">
                                                        {{ \Carbon\Carbon::parse($p->tanggal_bayar)->isoFormat('D MMM Y') }}
                                                        <span class="text-gray-300 mx-1">•</span>
                                                        Pukul: {{ \Carbon\Carbon::parse($p->created_at)->format('H:i') }}
                                                    </p>
                                                    @if ($p->keterangan)
                                                        <p class="text-xs text-gray-400">{{ $p->keterangan }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <span
                                                    class="text-xs text-gray-400 block">{{ $p->akunKas->nama_akun ?? '-' }}</span>
                                                @if ($p->status_validasi === 'ditolak')
                                                    <span
                                                        class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-[10px] font-medium mt-1 inline-block">Ditolak</span>
                                                @elseif($p->status_validasi === 'pending')
                                                    <span
                                                        class="px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full text-[10px] font-medium mt-1 inline-block">Menunggu</span>
                                                @else
                                                    <span
                                                        class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-[10px] font-medium mt-1 inline-block">Disetujui</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8 text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <p>Belum ada riwayat tagihan</p>
            </div>
        @endif
    </div>

    {{-- MODAL Semua Riwayat --}}
    <div id="modalRiwayat" class="hidden fixed inset-0 z-50 items-center justify-center bg-black bg-opacity-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-screen overflow-hidden flex flex-col">

            {{-- Modal Header --}}
            <div class="flex justify-between items-center p-6 border-b">
                <h2 class="text-lg font-bold text-gray-800">Semua Riwayat Tagihan</h2>
                <button onclick="tutupModalRiwayat()" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="overflow-y-auto p-6 space-y-3">
                @forelse($riwayatTagihan as $tagihan)
                    <div class="border rounded-lg p-4">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <p class="font-semibold text-gray-800">
                                    {{ \Carbon\Carbon::create()->month($tagihan->bulan_periode)->isoFormat('MMMM') }}
                                    {{ $tagihan->tahun_periode }}
                                </p>
                                <div class="flex items-center gap-2 mt-1">
                                    @if ($tagihan->pencatatanMeter)
                                        <p class="text-sm text-gray-500">
                                            💧 {{ number_format($tagihan->pencatatanMeter->volume_m3, 2) }} m³
                                        </p>
                                    @endif
                                    <span class="text-gray-300">•</span>
                                    <a href="{{ route('pelanggan.tagihan.surat', $tagihan->id) }}"
                                        class="text-blue-600 hover:text-blue-800 text-xs font-semibold flex items-center gap-1">
                                        Surat Tagihan
                                    </a>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-gray-800">
                                    Rp {{ number_format($tagihan->total_tagihan, 0, ',', '.') }}
                                </p>
                                @if ($tagihan->status_bayar === 'lunas')
                                    <span
                                        class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-medium">Lunas</span>
                                @elseif($tagihan->status_bayar === 'mencicil')
                                    <span
                                        class="px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">Mencicil</span>
                                @else
                                    <span
                                        class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-xs font-medium">Belum
                                        Lunas</span>
                                @endif
                            </div>
                        </div>

                        {{-- Progress --}}
                        @if ($tagihan->total_tagihan > 0)
                            <div class="mb-2 mt-2">
                                <div class="w-full bg-gray-200 rounded-full h-1.5">
                                    <div class="h-1.5 rounded-full {{ $tagihan->status_bayar === 'lunas' ? 'bg-green-500' : 'bg-blue-500' }}"
                                        style="width: {{ ($tagihan->total_terbayar / $tagihan->total_tagihan) * 100 }}%">
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Detail --}}
                        <div class="grid grid-cols-3 gap-2 text-xs text-gray-500 mb-2 mt-2">
                            <div>
                                <span class="block text-gray-400">Biaya Beban</span>
                                Rp {{ number_format($tagihan->biaya_beban, 0, ',', '.') }}
                            </div>
                            <div>
                                <span class="block text-gray-400">Biaya Pemakaian</span>
                                Rp {{ number_format($tagihan->biaya_pemakaian, 0, ',', '.') }}
                            </div>
                            <div>
                                <span class="block text-gray-400">Sisa</span>
                                <span
                                    class="{{ $tagihan->sisa_tagihan > 0 ? 'text-red-600 font-semibold' : 'text-green-600' }}">
                                    Rp {{ number_format($tagihan->sisa_tagihan, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        {{-- Bukti Pembayaran --}}
                        @if ($tagihan->pembayaran->count() > 0)
                            <div class="border-t pt-2">
                                <p class="text-xs font-semibold text-gray-500 mb-2">Bukti Pembayaran:</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($tagihan->pembayaran as $p)
                                        <div class="flex items-center gap-2 bg-gray-50 rounded-lg p-2 text-xs">
                                            @if ($p->foto_bukti)
                                                {{-- INSTANCE 2: Diubah dari Tag <a> menjadi Button Pop-up --}}
                                                <button type="button"
                                                    onclick="bukaModalFoto('{{ asset('storage/' . $p->foto_bukti) }}')"
                                                    class="focus:outline-none">
                                                    <img src="{{ asset('storage/' . $p->foto_bukti) }}" alt="Bukti"
                                                        class="w-10 h-10 object-cover rounded border hover:opacity-80 cursor-pointer">
                                                </button>
                                            @else
                                                <div
                                                    class="w-10 h-10 bg-gray-200 rounded flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-gray-400" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                </div>
                                            @endif
                                            <div>
                                                 <p class="font-medium text-gray-700 flex items-center gap-1.5 flex-wrap">
                                                     <span>Rp {{ number_format($p->nominal_bayar, 0, ',', '.') }}</span>
                                                     @if ($p->arusKas?->no_bukti)
                                                         <span class="text-[9px] font-mono text-gray-500 bg-gray-100 px-1 py-0.5 rounded border border-gray-200">
                                                             {{ $p->arusKas->no_bukti }}
                                                         </span>
                                                     @endif
                                                 </p>
                                                <p class="text-gray-400">
                                                    {{ \Carbon\Carbon::parse($p->tanggal_bayar)->isoFormat('D MMM Y') }}
                                                    <span class="text-gray-300 mx-1">•</span>
                                                    Pukul: {{ \Carbon\Carbon::parse($p->created_at)->format('H:i') }}
                                                </p>
                                                @if ($p->status_validasi === 'ditolak')
                                                    <span
                                                        class="px-1.5 py-0.5 bg-red-100 text-red-700 rounded-full text-[10px] font-medium">Ditolak</span>
                                                @elseif($p->status_validasi === 'pending')
                                                    <span
                                                        class="px-1.5 py-0.5 bg-yellow-100 text-yellow-700 rounded-full text-[10px] font-medium">Menunggu</span>
                                                @else
                                                    <span
                                                        class="px-1.5 py-0.5 bg-green-100 text-green-700 rounded-full text-[10px] font-medium">Disetujui</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-center text-gray-400 py-4">Belum ada riwayat tagihan</p>
                @endforelse
            </div>

            {{-- Modal Footer --}}
            <div class="p-4 border-t">
                <button onclick="tutupModalRiwayat()"
                    class="w-full bg-gray-100 text-gray-700 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL Upload Bukti Pembayaran --}}
    @if ($tagihanAktif)
        <div id="modalUploadBukti"
            class="hidden fixed inset-0 z-50 items-center justify-center bg-black bg-opacity-50 p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md max-h-screen overflow-hidden flex flex-col">
                {{-- Modal Header --}}
                <div class="flex justify-between items-center p-6 border-b">
                    <h2 class="text-lg font-bold text-gray-800">Upload Bukti Pembayaran</h2>
                    <button onclick="tutupModalUploadBukti()" class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="overflow-y-auto p-6">
                    {{-- Tambahan proteksi Double Click Form Submission --}}
                    <form action="{{ route('pelanggan.pembayaran.submit') }}" method="POST"
                        enctype="multipart/form-data"
                        onsubmit="this.querySelector('button[type=submit]').disabled = true; this.querySelector('button[type=submit]').innerHTML = '⏳ Sedang Mengirim...'; return true;">
                        @csrf
                        <input type="hidden" name="tagihan_id" value="{{ $tagihanAktif->id }}">

                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Foto Bukti Transfer/Pembayaran <span class="text-red-500">*</span>
                            </label>
                            <input type="file" name="foto_bukti" accept="image/*" required
                                onchange="previewBukti(this)"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <p class="text-xs text-gray-400 mt-1">Format: JPG, PNG. Maks 5MB</p>
                            <div id="previewBuktiContainer" class="hidden mt-2">
                                <img id="previewBuktiImg" src="" alt="Preview"
                                    class="w-32 h-32 object-cover rounded-lg border">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Keterangan <span class="text-gray-400 font-normal">(Opsional)</span>
                            </label>
                            <input type="text" name="keterangan" placeholder="Contoh: Transfer BRI 10 April 2026"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
                            <p class="text-xs text-yellow-700">
                                ⚠️ Bukti yang dikirim akan diverifikasi oleh Admin. Pembayaran baru tercatat setelah
                                dikonfirmasi.
                            </p>
                        </div>

                        <button type="submit"
                            class="w-full bg-blue-600 text-white py-2.5 rounded-lg text-sm font-medium hover:bg-blue-700 transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                            Kirim Bukti Pembayaran
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL POP-UP PREVIEW KOTAK FIXED (TIDAK FULL SCREEN & BACKGROUND TRANSPARAN) --}}
    <div id="modalPreviewFoto"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden border flex flex-col animate-fade-in">
            <div class="flex justify-between items-center px-5 py-4 border-b bg-gray-50">
                <h3 class="text-sm font-bold text-gray-800">Pratinjau Bukti Pembayaran</h3>
                <button type="button" onclick="tutupModalFoto()"
                    class="text-gray-400 hover:text-gray-600 transition p-1.5 rounded-lg hover:bg-gray-200/70 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="p-5 flex justify-center bg-gray-100/30">
                <img id="imgPreviewModal" src="" alt="Bukti Pembayaran"
                    class="max-h-[55vh] max-w-full object-contain rounded-xl shadow-sm border bg-white">
            </div>
        </div>
    </div>

    {{-- Script Interaktif Modal & Preview Foto --}}
    <script>
        const modal = document.getElementById('modalRiwayat');

        // Fungsi Buka Modal
        function bukaModalRiwayat() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        // Fungsi Tutup Modal
        function tutupModalRiwayat() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        // Tutup modal kalau klik di luar area konten modal
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                tutupModalRiwayat();
            }
        });

        // Modal Upload Bukti
        const modalBukti = document.getElementById('modalUploadBukti');

        function bukaModalUploadBukti() {
            if (modalBukti) {
                modalBukti.classList.remove('hidden');
                modalBukti.classList.add('flex');
            }
        }

        function tutupModalUploadBukti() {
            if (modalBukti) {
                modalBukti.classList.add('hidden');
                modalBukti.classList.remove('flex');
            }
        }

        if (modalBukti) {
            modalBukti.addEventListener('click', function(e) {
                if (e.target === this) {
                    tutupModalUploadBukti();
                }
            });
        }

        @if ($errors->any())
            document.addEventListener('DOMContentLoaded', () => {
                bukaModalUploadBukti();
            });
        @endif

        // Preview Bukti Pembayaran Sebelum Upload
        function previewBukti(input) {
            const container = document.getElementById('previewBuktiContainer');
            const img = document.getElementById('previewBuktiImg');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    img.src = e.target.result;
                    container.classList.remove('hidden');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // KONTROL POP-UP INTERAKTIF PREVIEW FOTO BUKTI TRANSFER
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

        if (modalFoto) {
            modalFoto.addEventListener('click', function(e) {
                if (e.target === this || e.target.id === 'modalPreviewFoto') {
                    tutupModalFoto();
                }
            });
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                tutupModalFoto();
            }
        });
    </script>

@endsection
