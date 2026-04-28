@extends('layouts.pelanggan')
@section('title', 'Surat Tagihan Air')

@section('content')
    <div class="max-w-2xl mx-auto">
        {{-- Tombol Navigasi --}}
        <div class="flex items-center justify-between mb-4 sm:mb-6">
            <a href="{{ route('pelanggan.dashboard') }}"
                class="text-blue-700 hover:text-blue-900 text-sm font-medium flex items-center gap-2 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
                Kembali ke Dashboard
            </a>
            <button onclick="window.print()"
                class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg text-xs sm:text-sm font-medium flex items-center gap-2 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                    </path>
                </svg>
                Cetak / Simpan PDF
            </button>
        </div>

        {{-- Kertas Tagihan --}}
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden" id="area-print">
            {{-- Header Kertas --}}
            <div class="bg-blue-50 border-b border-blue-100 p-6 sm:p-8 text-center">
                <h2 class="text-xl sm:text-2xl font-bold text-blue-900 tracking-tight">SURAT TAGIHAN AIR</h2>
                <p class="text-sm text-blue-700 mt-1">KP-SPAMS Tirta Wening Desa Singodutan</p>
            </div>

            {{-- Identitas --}}
            <div class="p-6 sm:p-8 border-b border-gray-100 border-dashed">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500 text-xs mb-1">Nama Pelanggan</p>
                        <p class="font-bold text-gray-800 uppercase">
                            {{ $tagihan->pelanggan->user->nama_lengkap ?? 'Tanpa Nama' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-gray-500 text-xs mb-1">Kode Pelanggan</p>
                        <p class="font-bold text-blue-700 font-mono">{{ $tagihan->pelanggan->kode_pelanggan }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs mb-1">Periode Tagihan</p>
                        <p class="font-semibold text-gray-800">Bulan
                            {{ str_pad($tagihan->bulan_periode, 2, '0', STR_PAD_LEFT) }} Tahun {{ $tagihan->tahun_periode }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-gray-500 text-xs mb-1">Status</p>
                        @if ($tagihan->status_bayar === 'lunas')
                            <span
                                class="inline-block bg-green-100 text-green-700 font-bold px-3 py-1 rounded text-xs uppercase tracking-wide">Lunas</span>
                        @elseif($tagihan->status_bayar === 'mencicil')
                            <span
                                class="inline-block bg-yellow-100 text-yellow-700 font-bold px-3 py-1 rounded text-xs uppercase tracking-wide">Mencicil</span>
                        @else
                            <span
                                class="inline-block bg-red-100 text-red-700 font-bold px-3 py-1 rounded text-xs uppercase tracking-wide">Belum
                                Lunas</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Rincian Pemakaian --}}
            <div class="p-6 sm:p-8 bg-gray-50/50">
                <h3 class="text-sm font-bold text-gray-700 mb-4 border-b pb-2">Rincian Pemakaian Meteran</h3>
                <div class="flex justify-between items-center mb-2 text-sm text-gray-600">
                    <span>Meter Awal</span>
                    <span class="font-mono">{{ number_format($tagihan->pencatatanMeter->meter_awal, 0, ',', '.') }}
                        m³</span>
                </div>
                <div class="flex justify-between items-center mb-3 text-sm text-gray-600">
                    <span>Meter Akhir</span>
                    <span class="font-mono">{{ number_format($tagihan->pencatatanMeter->meter_akhir, 0, ',', '.') }}
                        m³</span>
                </div>
                <div
                    class="flex justify-between items-center p-3 bg-blue-50 border border-blue-100 rounded-lg text-sm text-blue-900 font-semibold">
                    <span>Total Volume Pemakaian</span>
                    <span class="font-mono text-base">{{ number_format($tagihan->pencatatanMeter->volume_m3, 0, ',', '.') }}
                        m³</span>
                </div>
            </div>

            {{-- Rincian Biaya --}}
            <div class="p-6 sm:p-8">
                <h3 class="text-sm font-bold text-gray-700 mb-4 border-b pb-2">Rincian Biaya</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>Biaya Beban Bulanan</span>
                        <span class="font-mono">Rp {{ number_format($tagihan->biaya_beban, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Biaya Pemakaian Air</span>
                        <span class="font-mono">Rp {{ number_format($tagihan->biaya_pemakaian, 0, ',', '.') }}</span>
                    </div>

                    <div class="border-t pt-3 mt-3">
                        <div class="flex justify-between items-center mb-2">
                            <span class="font-bold text-gray-800">Total Tagihan</span>
                            <span class="font-bold text-gray-800 font-mono">Rp
                                {{ number_format($tagihan->total_tagihan, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center mb-2 text-green-700">
                            <span>Telah Dibayar</span>
                            <span class="font-mono">- Rp {{ number_format($tagihan->total_terbayar, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center mt-4 pt-3 border-t-2 border-dashed">
                            <span class="text-lg font-bold text-red-600">Sisa Tunggakan</span>
                            <span class="text-lg font-bold text-red-600 font-mono">Rp
                                {{ number_format($tagihan->sisa_tagihan, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            @if ($tagihan->status_bayar !== 'lunas')
                <div class="bg-yellow-50 border-t border-yellow-100 p-4 text-center print-hide">
                    <p class="text-xs text-yellow-800 font-medium">Harap segera melunasi tagihan ini sebelum batas waktu
                        untuk menghindari denda atau pemutusan saluran.</p>
                </div>
            @endif
        </div>

        {{-- Script CSS agar saat dicetak yang muncul cuma Surat Tagihannya saja --}}
        <style>
            @media print {
                body * {
                    visibility: hidden;
                }

                #area-print,
                #area-print * {
                    visibility: visible;
                }

                #area-print {
                    position: absolute;
                    left: 0;
                    top: 0;
                    width: 100%;
                    box-shadow: none;
                    border: none;
                }

                .print-hide {
                    display: none !important;
                }
            }
        </style>
    </div>
@endsection
