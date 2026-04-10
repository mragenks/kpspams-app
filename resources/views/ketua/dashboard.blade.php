@extends('layouts.admin')
@section('title', 'Dashboard Ketua')

@section('content')

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6 flex items-center">
            <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500">Pelanggan Aktif</p>
                <p class="text-2xl font-bold text-gray-700">{{ $totalPelanggan }}</p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 flex items-center">
            <div class="p-3 rounded-full bg-red-100 text-red-600 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500">Belum Lunas</p>
                <p class="text-2xl font-bold text-red-600">{{ $tagihanBelumLunas }}</p>
                <p class="text-xs text-gray-400">
                    Piutang: Rp {{ number_format($totalPiutang, 0, ',', '.') }}
                </p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 flex items-center">
            <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total Saldo</p>
                <p class="text-2xl font-bold text-green-600">
                    Rp {{ number_format($totalSaldo, 0, ',', '.') }}
                </p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 flex items-center">
            <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500">Pemasukan Bulan Ini</p>
                <p class="text-2xl font-bold text-purple-600">
                    Rp {{ number_format($pemasukanBulanIni, 0, ',', '.') }}
                </p>
            </div>
        </div>
    </div>

    {{-- Saldo Per Akun --}}
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-base font-semibold text-gray-700 mb-4">Saldo Per Akun Kas</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach ($akunKasList as $ak)
                <div class="border rounded-lg p-4 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div
                            class="p-2 rounded-full {{ $ak->jenis_akun === 'tunai' ? 'bg-blue-100 text-blue-600' : 'bg-purple-100 text-purple-600' }}">
                            @if ($ak->jenis_akun === 'tunai')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            @else
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                            @endif
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">{{ $ak->nama_akun }}</p>
                            <p class="text-xs text-gray-400 capitalize">{{ $ak->jenis_akun }}</p>
                        </div>
                    </div>
                    <p class="font-bold text-green-600">
                        Rp {{ number_format($ak->saldo, 0, ',', '.') }}
                    </p>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Info --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <p class="text-sm text-blue-700">
            💡 Sebagai Ketua, Anda dapat memantau laporan keuangan dan buku kas melalui menu di sidebar.
            Untuk pengelolaan data, hubungi Admin.
        </p>
    </div>

@endsection
