@extends('layouts.admin')
@section('title', 'Tambah Aset')

@section('content')
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow p-4 sm:p-6">
        <h2 class="text-lg font-semibold text-gray-700 mb-6">Tambah Aset Baru</h2>

        <form action="{{ route('admin.inventaris.store') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nama Barang <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nama_barang" value="{{ old('nama_barang') }}"
                    placeholder="Contoh: Mesin Pompa Air, Pipa PVC 3 inch..."
                    class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition {{ $errors->has('nama_barang') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                @error('nama_barang')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Jenis Aset <span class="text-red-500">*</span>
                    </label>
                    <select name="jenis_aset"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="tetap" {{ old('jenis_aset') === 'tetap' ? 'selected' : '' }}>
                            Aset Tetap (Pompa, Bangunan, dll)
                        </option>
                        <option value="tidak_tetap" {{ old('jenis_aset') === 'tidak_tetap' ? 'selected' : '' }}>
                            Aset Tidak Tetap (Pipa, Kaporit, dll)
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Kondisi <span class="text-red-500">*</span>
                    </label>
                    <select name="kondisi"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="baik" {{ old('kondisi') === 'baik' ? 'selected' : '' }}>Baik</option>
                        <option value="perlu_perbaikan" {{ old('kondisi') === 'perlu_perbaikan' ? 'selected' : '' }}>Perlu
                            Perbaikan</option>
                        <option value="rusak" {{ old('kondisi') === 'rusak' ? 'selected' : '' }}>Rusak</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Jumlah <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="jumlah" value="{{ old('jumlah', 1) }}" min="1"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition {{ $errors->has('jumlah') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                    @error('jumlah')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Perolehan</label>
                    <input type="date" name="tanggal_perolehan" value="{{ old('tanggal_perolehan', date('Y-m-d')) }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
                <input type="text" name="lokasi" value="{{ old('lokasi') }}"
                    placeholder="Contoh: Rumah Pompa RT 04, Gudang Desa..."
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                <textarea name="keterangan" rows="3" placeholder="Deskripsi tambahan (opsional)"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('keterangan') }}</textarea>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-6">
                <p class="text-xs text-blue-700">
                    💡 Kode barang akan dibuat otomatis: <strong>AT-XXX</strong> untuk aset tetap,
                    <strong>ATT-XXX</strong> untuk aset tidak tetap.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <button type="submit"
                    class="w-full sm:w-auto text-center bg-blue-600 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                    Simpan
                </button>
                <a href="{{ route('admin.inventaris.index') }}"
                    class="w-full sm:w-auto text-center bg-gray-500 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-600 transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection
