@extends('layouts.admin')
@section('title', 'Inventaris Aset')

@section('content')

    {{-- Summary Cards (1 Baris isi 3 - Mobile Optimized) --}}
    <div class="grid grid-cols-3 gap-2 sm:gap-4 mb-6">

        <div
            class="bg-white rounded-lg shadow p-2 sm:p-4 flex flex-col md:flex-row items-center justify-center md:justify-start text-center md:text-left">
            <div class="p-1.5 sm:p-2.5 rounded-full bg-blue-100 text-blue-600 mb-1.5 md:mb-0 md:mr-3">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
            <div>
                <p class="text-[9px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wide md:mb-0.5 truncate">
                    <span class="hidden sm:inline">Aset </span>Tetap
                </p>
                <p class="text-sm sm:text-lg font-bold text-gray-800">{{ $totalTetap }}</p>
            </div>
        </div>

        <div
            class="bg-white rounded-lg shadow p-2 sm:p-4 flex flex-col md:flex-row items-center justify-center md:justify-start text-center md:text-left">
            <div class="p-1.5 sm:p-2.5 rounded-full bg-green-100 text-green-600 mb-1.5 md:mb-0 md:mr-3">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                </svg>
            </div>
            <div>
                <p class="text-[9px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wide md:mb-0.5 truncate">
                    <span class="hidden sm:inline">Aset </span>Tidak Tetap
                </p>
                <p class="text-sm sm:text-lg font-bold text-gray-800">{{ $totalTidakTetap }}</p>
            </div>
        </div>

        <div
            class="bg-white rounded-lg shadow p-2 sm:p-4 flex flex-col md:flex-row items-center justify-center md:justify-start text-center md:text-left">
            <div class="p-1.5 sm:p-2.5 rounded-full bg-red-100 text-red-600 mb-1.5 md:mb-0 md:mr-3">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div>
                <p class="text-[9px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wide md:mb-0.5 truncate">
                    <span class="hidden sm:inline">Aset </span>Rusak
                </p>
                <p class="text-sm sm:text-lg font-bold text-red-600">{{ $totalRusak }}</p>
            </div>
        </div>

    </div>

    {{-- Filter & Tombol Tambah (Desain Kompak) --}}
    <div class="bg-white rounded-lg shadow p-3 mb-6 border border-gray-100">
        <form method="GET" action="{{ route('admin.inventaris.index') }}"
            class="flex flex-col md:flex-row gap-3 md:items-end">

            <div class="w-full md:w-40">
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Jenis Aset</label>
                <select name="jenis"
                    class="w-full border border-gray-300 rounded-md px-2.5 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="">Semua Jenis</option>
                    <option value="tetap" {{ $jenis === 'tetap' ? 'selected' : '' }}>Aset Tetap</option>
                    <option value="tidak_tetap" {{ $jenis === 'tidak_tetap' ? 'selected' : '' }}>Tidak Tetap</option>
                </select>
            </div>

            <div class="w-full md:w-40">
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Kondisi</label>
                <select name="kondisi"
                    class="w-full border border-gray-300 rounded-md px-2.5 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="">Semua Kondisi</option>
                    <option value="baik" {{ $kondisi === 'baik' ? 'selected' : '' }}>Baik</option>
                    <option value="perlu_perbaikan" {{ $kondisi === 'perlu_perbaikan' ? 'selected' : '' }}>Perbaikan
                    </option>
                    <option value="rusak" {{ $kondisi === 'rusak' ? 'selected' : '' }}>Rusak</option>
                </select>
            </div>

            <div class="flex flex-row gap-2 w-full md:w-auto mt-1 md:mt-0 md:ml-auto">
                <button type="submit"
                    class="flex-1 md:flex-none flex justify-center items-center bg-gray-800 text-white px-4 py-1.5 rounded-md text-xs font-medium hover:bg-gray-900 transition">
                    Filter
                </button>
                <a href="{{ route('admin.inventaris.create') }}"
                    class="flex-1 md:flex-none flex justify-center items-center bg-blue-600 text-white px-4 py-1.5 rounded-md text-xs font-medium hover:bg-blue-700 transition gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah
                </a>
            </div>

        </form>
    </div>

    {{-- Tabel --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 sm:p-6 border-b">
            <h2 class="text-lg font-semibold text-gray-700">Daftar Inventaris Aset</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-600 uppercase text-xs tracking-wider border-b">
                    <tr>
                        <th class="px-4 py-4 sm:px-6">No</th>
                        <th class="px-4 py-4 sm:px-6">Kode</th>
                        <th class="px-4 py-4 sm:px-6 min-w-[150px]">Nama Barang</th>
                        <th class="hidden md:table-cell px-6 py-4">Jenis</th>
                        <th class="px-4 py-4 sm:px-6">Stok</th>
                        <th class="hidden sm:table-cell px-6 py-4">Kondisi</th>
                        <th class="hidden lg:table-cell px-6 py-4">Lokasi</th>
                        <th class="px-4 py-4 sm:px-6 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($inventaris as $index => $item)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-4 sm:px-6 text-gray-500 whitespace-nowrap">
                                {{ ($inventaris->currentPage() - 1) * $inventaris->perPage() + $index + 1 }}
                            </td>
                            <td class="px-4 py-4 sm:px-6 font-mono text-xs text-blue-600 font-semibold whitespace-nowrap">
                                {{ $item->kode_barang }}
                            </td>
                            <td class="px-4 py-4 sm:px-6 font-medium text-gray-800">
                                {{ $item->nama_barang }}
                            </td>
                            <td class="hidden md:table-cell px-6 py-4 whitespace-nowrap">
                                @if ($item->jenis_aset === 'tetap')
                                    <span
                                        class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">Tetap</span>
                                @else
                                    <span
                                        class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-medium">Tidak
                                        Tetap</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 sm:px-6 font-semibold text-gray-700 whitespace-nowrap">
                                {{ $item->stok_saat_ini }}
                                @if ($item->jenis_aset === 'tidak_tetap' && $item->stok_saat_ini <= 5)
                                    <span class="text-red-500 text-xs block mt-1">⚠️ Menipis</span>
                                @endif
                            </td>
                            <td class="hidden sm:table-cell px-6 py-4 whitespace-nowrap">
                                @if ($item->kondisi === 'baik')
                                    <span
                                        class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">Baik</span>
                                @elseif($item->kondisi === 'perlu_perbaikan')
                                    <span
                                        class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">Perbaikan</span>
                                @else
                                    <span
                                        class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium">Rusak</span>
                                @endif
                            </td>
                            <td class="hidden lg:table-cell px-6 py-4 text-gray-500 truncate max-w-[150px]">
                                {{ $item->lokasi ?? '-' }}
                            </td>
                            <td class="px-4 py-4 sm:px-6">
                                <div class="flex flex-col sm:flex-row gap-2 justify-center">
                                    <a href="{{ route('admin.inventaris.show', $item) }}"
                                        class="text-center px-3 py-1.5 bg-blue-600 text-white rounded text-xs font-medium hover:bg-blue-700 transition">
                                        Detail
                                    </a>
                                    <a href="{{ route('admin.inventaris.edit', $item) }}"
                                        class="text-center px-3 py-1.5 bg-yellow-500 text-white rounded text-xs font-medium hover:bg-yellow-600 transition">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.inventaris.destroy', $item) }}" method="POST"
                                        class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="w-full btn-hapus px-3 py-1.5 bg-red-600 text-white rounded text-xs font-medium hover:bg-red-700 transition">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-10 text-center text-gray-400">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                    <p>Belum ada data inventaris</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($inventaris->hasPages())
            <div class="p-4 border-t">
                {{ $inventaris->links() }}
            </div>
        @endif
    </div>
@endsection
