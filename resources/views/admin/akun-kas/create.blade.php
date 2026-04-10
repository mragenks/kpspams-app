@extends('layouts.admin')
@section('title', 'Tambah Akun Kas')

@section('content')
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow p-4 sm:p-6">
        <h2 class="text-lg font-semibold text-gray-700 mb-6">Tambah Akun Kas</h2>

        <form action="{{ route('admin.akun-kas.store') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nama Akun <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nama_akun" value="{{ old('nama_akun') }}" placeholder="Contoh: Kas Tunai Bendahara"
                    class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition {{ $errors->has('nama_akun') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                @error('nama_akun')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Jenis Akun <span class="text-red-500">*</span>
                </label>
                <select name="jenis_akun"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="tunai" {{ old('jenis_akun') === 'tunai' ? 'selected' : '' }}>Tunai</option>
                    <option value="bank" {{ old('jenis_akun') === 'bank' ? 'selected' : '' }}>Bank</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Saldo Awal <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-2 text-sm text-gray-500">Rp</span>
                    <input type="number" name="saldo" value="{{ old('saldo', 0) }}" min="0"
                        class="w-full border rounded-lg pl-10 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition {{ $errors->has('saldo') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                </div>
                @error('saldo')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                <textarea name="keterangan" rows="3" placeholder="Opsional..."
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('keterangan') }}</textarea>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <button type="submit"
                    class="w-full sm:w-auto text-center bg-blue-600 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                    Simpan
                </button>
                <a href="{{ route('admin.akun-kas.index') }}"
                    class="w-full sm:w-auto text-center bg-gray-500 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-600 transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection
