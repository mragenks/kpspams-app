@extends('layouts.admin')
@section('title', 'Akun Kas')

@section('content')

    {{-- Summary Cards (1 Baris isi 3 - Mobile Optimized) --}}
    <div class="grid grid-cols-3 gap-2 sm:gap-4 mb-6">

        <div
            class="bg-white rounded-lg shadow p-2 sm:p-4 flex flex-col md:flex-row items-center justify-center md:justify-start text-center md:text-left overflow-hidden">
            <div class="p-1.5 sm:p-2.5 rounded-full bg-green-100 text-green-600 mb-1.5 md:mb-0 md:mr-3 shrink-0">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <div class="min-w-0 w-full">
                <p class="text-[9px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wide md:mb-0.5 truncate">
                    <span class="hidden sm:inline">Total </span>Saldo
                </p>
                <p class="text-xs sm:text-lg font-bold text-gray-700 truncate"
                    title="Rp {{ number_format($akunKas->sum('saldo'), 0, ',', '.') }}">
                    <span class="hidden sm:inline">Rp </span>{{ number_format($akunKas->sum('saldo'), 0, ',', '.') }}
                </p>
            </div>
        </div>

        <div
            class="bg-white rounded-lg shadow p-2 sm:p-4 flex flex-col md:flex-row items-center justify-center md:justify-start text-center md:text-left overflow-hidden">
            <div class="p-1.5 sm:p-2.5 rounded-full bg-blue-100 text-blue-600 mb-1.5 md:mb-0 md:mr-3 shrink-0">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="min-w-0 w-full">
                <p class="text-[9px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wide md:mb-0.5 truncate">
                    <span class="hidden sm:inline">Saldo </span>Tunai
                </p>
                <p class="text-xs sm:text-lg font-bold text-gray-700 truncate"
                    title="Rp {{ number_format($akunKas->where('jenis_akun', 'tunai')->sum('saldo'), 0, ',', '.') }}">
                    <span class="hidden sm:inline">Rp
                    </span>{{ number_format($akunKas->where('jenis_akun', 'tunai')->sum('saldo'), 0, ',', '.') }}
                </p>
            </div>
        </div>

        <div
            class="bg-white rounded-lg shadow p-2 sm:p-4 flex flex-col md:flex-row items-center justify-center md:justify-start text-center md:text-left overflow-hidden">
            <div class="p-1.5 sm:p-2.5 rounded-full bg-purple-100 text-purple-600 mb-1.5 md:mb-0 md:mr-3 shrink-0">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
            </div>
            <div class="min-w-0 w-full">
                <p class="text-[9px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wide md:mb-0.5 truncate">
                    <span class="hidden sm:inline">Saldo </span>Bank
                </p>
                <p class="text-xs sm:text-lg font-bold text-gray-700 truncate"
                    title="Rp {{ number_format($akunKas->where('jenis_akun', 'bank')->sum('saldo'), 0, ',', '.') }}">
                    <span class="hidden sm:inline">Rp
                    </span>{{ number_format($akunKas->where('jenis_akun', 'bank')->sum('saldo'), 0, ',', '.') }}
                </p>
            </div>
        </div>

    </div>

    {{-- Tabel --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 sm:p-6 border-b flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h2 class="text-lg font-semibold text-gray-700">Daftar Akun Kas</h2>
            <a href="{{ route('admin.akun-kas.create') }}"
                class="w-full sm:w-auto bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition inline-flex justify-center items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Akun
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-600 uppercase text-[10px] sm:text-xs tracking-wider border-b">
                    <tr>
                        <th class="px-4 py-3 sm:px-6">No</th>
                        <th class="px-4 py-3 sm:px-6 min-w-[150px]">Nama Akun</th>
                        <th class="hidden sm:table-cell px-6 py-3">Jenis</th>
                        <th class="px-4 py-3 sm:px-6">Saldo</th>
                        <th class="hidden md:table-cell px-6 py-3">Keterangan</th>
                        <th class="px-4 py-3 sm:px-6">Status</th>
                        <th class="px-4 py-3 sm:px-6 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($akunKas as $index => $ak)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 sm:px-6 text-gray-500">{{ $index + 1 }}</td>
                            <td class="px-4 py-3 sm:px-6 font-medium text-gray-800">
                                {{ $ak->nama_akun }}
                                <div class="block sm:hidden mt-1 text-[10px] text-gray-400">
                                    {{ $ak->jenis_akun === 'tunai' ? 'Tunai' : 'Bank' }}
                                </div>
                            </td>
                            <td class="hidden sm:table-cell px-6 py-3">
                                @if ($ak->jenis_akun === 'tunai')
                                    <span
                                        class="px-2.5 py-1 bg-blue-100 text-blue-700 rounded-full text-[10px] font-bold uppercase tracking-wide">Tunai</span>
                                @else
                                    <span
                                        class="px-2.5 py-1 bg-purple-100 text-purple-700 rounded-full text-[10px] font-bold uppercase tracking-wide">Bank</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 sm:px-6 font-semibold text-green-600 whitespace-nowrap">
                                Rp {{ number_format($ak->saldo, 0, ',', '.') }}
                            </td>
                            <td class="hidden md:table-cell px-6 py-3 text-gray-500 text-xs">{{ $ak->keterangan ?? '-' }}
                            </td>
                            <td class="px-4 py-3 sm:px-6">
                                @if ($ak->is_active)
                                    <span
                                        class="px-2.5 py-1 bg-green-100 text-green-700 rounded-full text-[10px] font-bold uppercase tracking-wide">Aktif</span>
                                @else
                                    <span
                                        class="px-2.5 py-1 bg-red-100 text-red-700 rounded-full text-[10px] font-bold uppercase tracking-wide">Non-aktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 sm:px-6">
                                <div class="flex flex-col sm:flex-row gap-2 justify-center">
                                    <a href="{{ route('admin.akun-kas.edit', $ak) }}"
                                        class="text-center px-3 py-1.5 bg-yellow-500 text-white rounded text-[10px] sm:text-xs font-medium hover:bg-yellow-600 transition shadow-sm">
                                        Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-gray-400">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                    <p>Belum ada akun kas</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
