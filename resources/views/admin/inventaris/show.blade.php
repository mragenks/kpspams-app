@extends('layouts.admin')
@section('title', 'Detail Aset')

@section('content')
    <div class="max-w-3xl mx-auto space-y-6">

        {{-- Info Aset --}}
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <div class="flex justify-between items-center mb-4 border-b pb-4 sm:border-none sm:pb-0">
                <h2 class="text-lg font-semibold text-gray-700">Detail Aset</h2>
                <a href="{{ route('admin.inventaris.index') }}"
                    class="text-sm text-blue-600 hover:text-blue-800 transition flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span class="hidden sm:inline">Kembali</span>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide">Kode Barang</p>
                    <p class="font-mono font-semibold text-blue-600 text-lg">{{ $inventaris->kode_barang }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Jenis Aset</p>
                    @if ($inventaris->jenis_aset === 'tetap')
                        <span
                            class="inline-block px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-semibold tracking-wide">Aset
                            Tetap</span>
                    @else
                        <span
                            class="inline-block px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-semibold tracking-wide">Aset
                            Tidak Tetap</span>
                    @endif
                </div>
            </div>

            <div class="mb-6">
                <p class="text-xs text-gray-400 uppercase tracking-wide">Nama Barang</p>
                <p class="font-semibold text-gray-800 text-xl">{{ $inventaris->nama_barang }}</p>
            </div>

            <div
                class="grid grid-cols-3 bg-gray-50 border border-gray-100 rounded-lg py-3 mb-6 divide-x divide-gray-200 shadow-sm">
                <div class="text-center px-1">
                    <p class="text-[9px] sm:text-xs text-gray-500 uppercase tracking-wide mb-0.5">Stok Awal</p>
                    <p class="text-xl sm:text-2xl font-bold text-gray-700">{{ $inventaris->stok_awal }}</p>
                </div>

                <div class="text-center px-1">
                    <p class="text-[9px] sm:text-xs text-gray-500 uppercase tracking-wide mb-0.5">Saat Ini</p>
                    <p class="text-xl sm:text-2xl font-bold text-blue-600">{{ $inventaris->stok_saat_ini }}</p>
                    @if ($inventaris->stok_saat_ini <= $inventaris->stok_minimum)
                        <span class="inline-block mt-1 px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-[10px] font-semibold">Stok Menipis</span>
                    @endif
                </div>

                <div class="text-center px-1 flex flex-col justify-center items-center">
                    <p class="text-[9px] sm:text-xs text-gray-500 uppercase tracking-wide mb-1">Kondisi</p>
                    @if ($inventaris->kondisi === 'baik')
                        <span
                            class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-[10px] font-semibold">Baik</span>
                    @elseif($inventaris->kondisi === 'perlu_perbaikan')
                        <span
                            class="px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full text-[10px] font-semibold">Perbaikan</span>
                    @else
                        <span
                            class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-[10px] font-semibold">Rusak</span>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide">Lokasi</p>
                    <p class="text-gray-800 font-medium">{{ $inventaris->lokasi ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide">Tanggal Perolehan</p>
                    <p class="text-gray-800 font-medium whitespace-nowrap">
                        {{ $inventaris->tanggal_perolehan
                            ? \Carbon\Carbon::parse($inventaris->tanggal_perolehan)->isoFormat('D MMMM Y') . ' pukul ' . $inventaris->created_at->format('H:i')
                            : '-' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide">Batas Stok Menipis</p>
                    <p class="text-gray-800 font-medium font-mono text-sm">{{ $inventaris->stok_minimum }} unit</p>
                </div>
            </div>

            @if ($inventaris->keterangan)
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Keterangan</p>
                    <p class="text-gray-600 text-sm leading-relaxed">{{ $inventaris->keterangan }}</p>
                </div>
            @endif
        </div>

        {{-- Form Mutasi Stok --}}
        <div class="bg-white rounded-lg shadow p-4 sm:p-6 border-l-4 border-blue-500">
            <h3 class="text-base font-semibold text-gray-800 mb-4">Catat Mutasi Stok</h3>

            {{-- Pesan Peringatan Jika Stok Tidak Cukup --}}
            @if (session('error'))
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-3 mb-4 rounded-r-lg text-sm">
                    <p class="font-bold">Gagal!</p>
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <form action="{{ route('admin.inventaris.mutasi', $inventaris) }}" method="POST">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Jenis Mutasi <span class="text-red-500">*</span>
                        </label>
                        <select name="jenis_mutasi" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                            <option value="masuk">Barang Masuk (Beli Baru / Penambahan)</option>
                            <option value="keluar">Barang Keluar (Pemasangan / Pemakaian)</option>
                            <option value="pengembalian">Pengembalian (Sisa Lapangan / Batal Pakai)</option>
                        </select>
                        @error('jenis_mutasi')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Jumlah Mutasi <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="jumlah" value="{{ old('jumlah', 1) }}" min="1" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('jumlah')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Keterangan <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="keterangan" value="{{ old('keterangan') }}" required
                            placeholder="Contoh: Pembelian baru, Pemakaian lapangan..."
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('keterangan')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Tanggal <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        @error('tanggal')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <button type="submit"
                    class="w-full sm:w-auto flex justify-center items-center gap-2 bg-blue-600 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4">
                        </path>
                    </svg>
                    Catat Mutasi
                </button>
            </form>
        </div>

        {{-- Riwayat Mutasi --}}
        @if ($inventaris->riwayat->count() > 0)
            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-4 border-b pb-2">Riwayat Mutasi Stok</h3>

                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="w-full text-sm text-left">
                        <thead
                            class="bg-gray-50 text-gray-600 uppercase text-[10px] tracking-wider border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3">No</th>
                                <th class="px-4 py-3 whitespace-nowrap">Tanggal</th>
                                <th class="px-4 py-3">Jenis</th>
                                <th class="px-4 py-3 text-center">Jumlah</th>
                                <th class="px-4 py-3 text-center whitespace-nowrap">Sblm ➔ Ssdh</th>
                                <th class="px-4 py-3">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($inventaris->riwayat->sortByDesc('tanggal') as $index => $r)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-700 font-medium">
                                        {{ \Carbon\Carbon::parse($r->tanggal)->isoFormat('D MMM Y') }}
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($r->jenis_mutasi === 'masuk')
                                            <span
                                                class="inline-block px-2 py-1 bg-green-100 text-green-700 rounded text-[10px] font-bold uppercase tracking-wider">Masuk</span>
                                        @elseif($r->jenis_mutasi === 'keluar')
                                            <span
                                                class="inline-block px-2 py-1 bg-red-100 text-red-700 rounded text-[10px] font-bold uppercase tracking-wider">Keluar</span>
                                        @elseif($r->jenis_mutasi === 'pengembalian')
                                            <span
                                                class="inline-block px-2 py-1 bg-blue-100 text-blue-700 rounded text-[10px] font-bold uppercase tracking-wider">Pengembalian</span>
                                        @else
                                            <span
                                                class="inline-block px-2 py-1 bg-gray-100 text-gray-700 rounded text-[10px] font-bold uppercase tracking-wider">Penyesuaian</span>
                                        @endif
                                    </td>

                                    {{-- Kolom jumlah mutasi: Hijau (+), Merah (-) --}}
                                    <td
                                        class="px-4 py-3 font-bold text-center {{ in_array($r->jenis_mutasi, ['masuk', 'pengembalian']) ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $r->jumlah }}
                                    </td>

                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        <span class="text-gray-500">{{ $r->stok_sebelum }}</span>
                                        <span class="mx-1 text-gray-300">➔</span>
                                        <span class="font-semibold text-blue-600">{{ $r->stok_sesudah }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 text-xs min-w-[150px]">{{ $r->keterangan }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    </div>
@endsection
