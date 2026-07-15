@extends('layouts.admin')
@section('title', 'Edit Data Pelanggan')

@section('content')
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow p-4 sm:p-6">
        <h2 class="text-lg font-semibold text-gray-700 mb-6">Edit Data Pelanggan</h2>

        <form action="{{ route('admin.pelanggan.update', $pelanggan) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Kode Pelanggan</label>
                <input type="text" value="{{ $pelanggan->kode_pelanggan }}" disabled
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-500 cursor-not-allowed">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nama Pelanggan <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nama_pelanggan"
                    value="{{ old('nama_pelanggan', $pelanggan->user->nama_lengkap) }}"
                    class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition {{ $errors->has('nama_pelanggan') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                @error('nama_pelanggan')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">NIK</label>
                <input type="text" name="nik" value="{{ old('nik', $pelanggan->nik ?? '') }}"
                    placeholder="Contoh: 3312345678901234" maxlength="16"
                    class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition {{ $errors->has('nik') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                @error('nik')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor HP / WhatsApp</label>
                <input type="text" name="no_hp" value="{{ old('no_hp', $pelanggan->user->no_hp ?? '') }}"
                    placeholder="Contoh: 081234567890" maxlength="15"
                    class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition {{ $errors->has('no_hp') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                @error('no_hp')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Meteran Awal</label>
                <input type="text" value="{{ $pelanggan->meteran_awal }}" disabled
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-500 cursor-not-allowed">
                <input type="hidden" name="meteran_awal" value="{{ $pelanggan->meteran_awal }}">
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap</label>
                <textarea name="alamat" rows="3"
                    class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition {{ $errors->has('alamat') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">{{ old('alamat', $pelanggan->user->alamat) }}</textarea>
                @error('alamat')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <button type="submit"
                    class="w-full sm:w-auto text-center bg-blue-600 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                    Update Data
                </button>
                <a href="{{ route('admin.pelanggan.index') }}"
                    class="w-full sm:w-auto text-center bg-gray-500 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-600 transition">
                    Batal
                </a>
            </div>
        </form>

        {{-- Form Hapus Pelanggan --}}
        <div class="mt-6 pt-6 border-t border-gray-100 flex justify-end">
            <form action="{{ route('admin.pelanggan.destroy', $pelanggan) }}" method="POST" class="w-full sm:w-auto">
                @csrf
                @method('DELETE')
                <button type="button"
                    class="btn-hapus w-full sm:w-auto flex items-center justify-center gap-2 bg-red-50 text-red-600 border border-red-200 px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-100 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Hapus Pelanggan
                </button>
            </form>
        </div>
    </div>
@endsection
