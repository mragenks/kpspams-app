@extends('layouts.admin')
@section('title', 'Tambah Pelanggan')

@section('content')
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow p-4 sm:p-6">
        <h2 class="text-lg font-semibold text-gray-700 mb-6">Tambah Pelanggan Baru</h2>

        <form action="{{ route('admin.pelanggan.store') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Pelanggan <span
                        class="text-red-500">*</span></label>
                <input type="text" name="nama_pelanggan" value="{{ old('nama_pelanggan') }}"
                    class="w-full border focus:outline-none focus:ring-2 focus:ring-blue-500 transition rounded-lg px-3 py-2 text-sm {{ $errors->has('nama_pelanggan') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                @error('nama_pelanggan')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">NIK</label>
                <input type="text" name="nik" value="{{ old('nik') }}" placeholder="Contoh: 3312345678901234"
                    maxlength="16"
                    class="w-full border focus:outline-none focus:ring-2 focus:ring-blue-500 transition rounded-lg px-3 py-2 text-sm {{ $errors->has('nik') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                @error('nik')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap</label>
                <textarea name="alamat" rows="3" placeholder="Masukkan alamat lengkap..."
                    class="w-full border focus:outline-none focus:ring-2 focus:ring-blue-500 transition rounded-lg px-3 py-2 text-sm {{ $errors->has('alamat') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">{{ old('alamat') }}</textarea>
                @error('alamat')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">No. HP / WhatsApp</label>
                <input type="text" name="no_hp" value="{{ old('no_hp') }}" placeholder="Contoh: 081234567890"
                    maxlength="15"
                    class="w-full border focus:outline-none focus:ring-2 focus:ring-blue-500 transition rounded-lg px-3 py-2 text-sm {{ $errors->has('no_hp') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                @error('no_hp')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Meteran Awal (m³) <span
                        class="text-red-500">*</span></label>
                <input type="text" name="meteran_awal" value="{{ old('meteran_awal', '0') }}"
                    class="w-full border focus:outline-none focus:ring-2 focus:ring-blue-500 transition rounded-lg px-3 py-2 text-sm {{ $errors->has('meteran_awal') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                @error('meteran_awal')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-400 mt-1">Angka awal pada meteran air pelanggan saat pertama dipasang</p>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-6">
                <p class="text-xs text-blue-700">
                    💡 <strong>Info:</strong> Akun login pelanggan akan dibuat otomatis.<br>
                    Username: <strong>Kode Pelanggan (PLG-XXX)</strong> | Password: <strong>pelanggan123</strong>
                </p>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <button type="submit"
                    class="w-full sm:w-auto text-center bg-blue-600 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                    Simpan Data
                </button>
                <a href="{{ route('admin.pelanggan.index') }}"
                    class="w-full sm:w-auto text-center bg-gray-500 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-600 transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection
