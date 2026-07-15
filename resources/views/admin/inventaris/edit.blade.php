@extends('layouts.admin')
@section('title', 'Edit Aset')

@section('content')
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow p-4 sm:p-6">
        <h2 class="text-lg font-semibold text-gray-700 mb-6">Edit Aset</h2>

        <form action="{{ route('admin.inventaris.update', $inventaris) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Kode Barang</label>
                <input type="text" disabled value="{{ $inventaris->kode_barang }}"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-500">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nama Barang <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nama_barang" value="{{ old('nama_barang', $inventaris->nama_barang) }}"
                    class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition {{ $errors->has('nama_barang') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                @error('nama_barang')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Aset</label>
                    <input type="text" disabled
                        value="{{ $inventaris->jenis_aset === 'tetap' ? 'Aset Tetap' : 'Aset Tidak Tetap' }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Kondisi <span class="text-red-500">*</span>
                    </label>
                    <select name="kondisi"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="baik" {{ $inventaris->kondisi === 'baik' ? 'selected' : '' }}>Baik</option>
                        <option value="perlu_perbaikan" {{ $inventaris->kondisi === 'perlu_perbaikan' ? 'selected' : '' }}>
                            Perlu Perbaikan</option>
                        <option value="rusak" {{ $inventaris->kondisi === 'rusak' ? 'selected' : '' }}>Rusak</option>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Stok Saat Ini</label>
                <input type="text" disabled value="{{ $inventaris->stok_saat_ini }}"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-500">
                <p class="text-xs text-gray-400 mt-1">
                    Stok diubah melalui fitur mutasi stok di halaman detail
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
                    <input type="text" name="lokasi" value="{{ old('lokasi', $inventaris->lokasi) }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Perolehan</label>
                    <input type="date" name="tanggal_perolehan"
                        value="{{ old('tanggal_perolehan', $inventaris->tanggal_perolehan ? \Carbon\Carbon::parse($inventaris->tanggal_perolehan)->format('Y-m-d') : '') }}"
                        readonly
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-500 cursor-not-allowed pointer-events-none">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Batas Stok Menipis (Peringatan) <span class="text-red-500">*</span>
                </label>
                <input type="number" name="stok_minimum" value="{{ old('stok_minimum', $inventaris->stok_minimum) }}" min="0"
                    class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition {{ $errors->has('stok_minimum') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                @error('stok_minimum')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                <textarea name="keterangan" rows="3"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('keterangan', $inventaris->keterangan) }}</textarea>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <button type="submit"
                    class="w-full sm:w-auto text-center bg-blue-600 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                    Update
                </button>
                <a href="{{ route('admin.inventaris.index') }}"
                    class="w-full sm:w-auto text-center bg-gray-500 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-600 transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection
