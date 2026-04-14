@extends('layouts.admin')
@section('title', 'Data Pelanggan')

@section('content')
    <div class="bg-white rounded-lg shadow overflow-hidden">
        {{-- Header --}}
        <div class="p-4 sm:p-6 border-b flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h2 class="text-lg font-semibold text-gray-700">Data Pelanggan</h2>
            <a href="{{ route('admin.pelanggan.create') }}"
                class="w-full sm:w-auto bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition inline-flex justify-center items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Pelanggan
            </a>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-600 uppercase text-[10px] sm:text-xs tracking-wider border-b">
                    <tr>
                        <th class="px-4 py-3 sm:px-6">No</th>
                        <th class="px-4 py-3 sm:px-6">Kode</th>
                        <th class="px-4 py-3 sm:px-6 min-w-[180px]">Nama Pelanggan</th>
                        <th class="hidden md:table-cell px-6 py-4">No. HP</th>
                        <th class="hidden lg:table-cell px-6 py-4">Alamat</th>
                        <th class="px-4 py-3 sm:px-6 text-center">Meter Akhir</th>
                        <th class="px-4 py-3 sm:px-6">Status</th>
                        <th class="px-4 py-3 sm:px-6 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($pelanggan as $index => $p)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 sm:px-6 text-gray-500 text-xs sm:text-sm">
                                {{ ($pelanggan->currentPage() - 1) * $pelanggan->perPage() + $index + 1 }}
                            </td>
                            <td class="px-4 py-3 sm:px-6 font-semibold text-blue-600 text-xs sm:text-sm whitespace-nowrap">
                                {{ $p->kode_pelanggan }}
                            </td>
                            <td class="px-4 py-3 sm:px-6">
                                <p class="font-medium text-gray-800 text-xs sm:text-sm">{{ $p->nama_pelanggan }}</p>
                                <div class="block md:hidden mt-1 text-[10px] text-gray-500 leading-tight">
                                    {{ $p->no_hp ?? '-' }} <br>
                                    <span class="truncate block max-w-[150px]">{{ $p->alamat ?? '-' }}</span>
                                </div>
                            </td>
                            <td class="hidden md:table-cell px-6 py-4 text-gray-600 whitespace-nowrap">
                                {{ $p->no_hp ?? '-' }}</td>
                            <td class="hidden lg:table-cell px-6 py-4 text-gray-600">
                                <span class="block truncate max-w-[200px]" title="{{ $p->alamat }}">
                                    {{ Str::limit($p->alamat, 30) ?? '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 sm:px-6 text-center">
                                <div class="inline-flex flex-col items-center">
                                    @if ($p->pencatatanMeter->first())
                                        <span class="font-mono font-bold text-gray-700 text-xs sm:text-sm">
                                            {{ number_format($p->pencatatanMeter->first()->meter_akhir, 0) }} <span
                                                class="text-[10px] font-normal">m³</span>
                                        </span>
                                        <span
                                            class="text-[9px] text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded mt-0.5 whitespace-nowrap">
                                            Bulan {{ $p->pencatatanMeter->first()->bulan_periode }}
                                        </span>
                                    @else
                                        <span class="font-mono font-bold text-gray-700 text-xs sm:text-sm">
                                            {{ number_format($p->meteran_awal, 0) }} <span
                                                class="text-[10px] font-normal">m³</span>
                                        </span>
                                        <span
                                            class="text-[9px] text-yellow-600 bg-yellow-50 px-1.5 py-0.5 rounded mt-0.5 font-medium border border-yellow-200">
                                            Baru
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 sm:px-6">
                                @if ($p->status === 'aktif')
                                    <span
                                        class="inline-block px-2.5 py-1 bg-green-100 text-green-700 rounded-full text-[10px] font-bold uppercase tracking-wide">Aktif</span>
                                @else
                                    <span
                                        class="inline-block px-2.5 py-1 bg-red-100 text-red-700 rounded-full text-[10px] font-bold uppercase tracking-wide">Non-aktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 sm:px-6">
                                <div class="flex flex-col sm:flex-row gap-1.5 justify-center">
                                    <a href="{{ route('admin.pelanggan.show', $p) }}"
                                        class="text-center px-2 py-1.5 sm:px-3 bg-blue-600 text-white rounded text-[10px] sm:text-xs font-medium hover:bg-blue-700 transition shadow-sm">
                                        Detail
                                    </a>
                                    <a href="{{ route('admin.pelanggan.edit', $p) }}"
                                        class="text-center px-2 py-1.5 sm:px-3 bg-yellow-500 text-white rounded text-[10px] sm:text-xs font-medium hover:bg-yellow-600 transition shadow-sm">
                                        Edit
                                    </a>
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
                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <p>Belum ada data pelanggan</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($pelanggan->hasPages())
            <div class="p-4 border-t">
                {{ $pelanggan->links() }}
            </div>
        @endif
    </div>
@endsection
