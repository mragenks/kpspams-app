@extends('layouts.admin')
@section('title', 'Dashboard Pencatat')

@section('content')
    {{-- Greeting Card --}}
    <div class="bg-blue-900 text-white rounded-2xl p-6 mb-6 shadow-md relative overflow-hidden">
        <div class="absolute right-0 bottom-0 opacity-15 transform translate-y-4 translate-x-4">
            <svg class="w-40 h-40" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
            </svg>
        </div>
        <div class="relative z-10">
            <p class="text-blue-200 text-sm font-medium uppercase tracking-wide">Petugas Lapangan</p>
            <h1 class="text-2xl sm:text-3xl font-black mt-1">{{ auth()->user()->nama_lengkap }}</h1>
            <p class="text-blue-100 text-xs sm:text-sm mt-2 opacity-90 max-w-md">
                Selamat bertugas! Silakan catat angka meteran air pelanggan secara berkala setiap bulannya.
            </p>
        </div>
    </div>

    {{-- Progress Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        
        <div class="bg-white rounded-xl shadow p-4 flex items-center hover:shadow-md transition">
            <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-3 flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-gray-500 truncate">Total Pelanggan Aktif</p>
                <p class="text-2xl font-black text-gray-800">{{ $totalPelanggan }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow p-4 flex items-center hover:shadow-md transition">
            <div class="p-3 rounded-full bg-green-100 text-green-600 mr-3 flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-gray-500 truncate">Sudah Dicatat ({{ \Carbon\Carbon::now()->isoFormat('MMMM') }})</p>
                <p class="text-2xl font-black text-green-600">{{ $recordedCount }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow p-4 flex items-center hover:shadow-md transition">
            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-3 flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-gray-500 truncate">Belum Dicatat ({{ \Carbon\Carbon::now()->isoFormat('MMMM') }})</p>
                <p class="text-2xl font-black text-yellow-600">{{ $pendingCount }}</p>
            </div>
        </div>

    </div>

    {{-- Call to Action Card --}}
    <div class="bg-white rounded-xl shadow p-6 text-center max-w-md mx-auto">
        <h2 class="text-lg font-bold text-gray-800 mb-2">Mulai Pencatatan Meter Air</h2>
        <p class="text-sm text-gray-600 mb-6">
            Klik tombol di bawah ini untuk melihat daftar pelanggan dan mulai merekam angka meteran air hari ini.
        </p>
        <a href="{{ route('pencatat.meter.index') }}"
            class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold px-6 py-3 rounded-lg text-sm transition shadow-md w-full">
            Buka Pencatatan Meter
        </a>
    </div>
@endsection
