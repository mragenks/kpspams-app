@extends('layouts.admin')
@section('title', 'Edit User Pengurus')

@section('content')
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow p-4 sm:p-6 border-t-4 border-yellow-500">
        <div class="flex justify-between items-center mb-6 border-b pb-4">
            <h2 class="text-lg font-semibold text-gray-800">Edit User (Admin / Ketua)</h2>
            <a href="{{ route('admin.user.show', $user->id) }}"
                class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
                <span class="hidden sm:inline">Kembali</span>
            </a>
        </div>

        <form action="{{ route('admin.user.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Bagian Akun Login --}}
            <div class="mb-8">
                <h3 class="text-xs font-bold text-gray-400 mb-4 uppercase tracking-widest flex items-center gap-2">
                    <span class="w-8 h-px bg-gray-200"></span> Data Akun Login
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Username <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="username" value="{{ old('username', $user->username) }}" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 transition">
                        @error('username')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role Akses <span
                                class="text-red-500">*</span></label>
                        <select name="role" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 transition bg-white">
                            <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin
                            </option>
                            <option value="ketua" {{ old('role', $user->role) === 'ketua' ? 'selected' : '' }}>Ketua
                            </option>
                        </select>
                        @error('role')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Bagian Biodata Pengurus --}}
            <div class="mb-6">
                <h3 class="text-xs font-bold text-gray-400 mb-4 uppercase tracking-widest flex items-center gap-2">
                    <span class="w-8 h-px bg-gray-200"></span> Profil Lengkap Pengurus
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="nama_pengurus"
                            value="{{ old('nama_pengurus', $user->pengurus->nama_pengurus ?? '') }}" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 transition">
                        @error('nama_pengurus')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
                        <input type="text" name="jabatan" value="{{ old('jabatan', $user->pengurus->jabatan ?? '') }}"
                            placeholder="Cont. Sekretaris, Bendahara..."
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 transition">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nomor WhatsApp / HP</label>
                    <input type="text" name="no_hp" value="{{ old('no_hp', $user->pengurus->no_hp ?? '') }}"
                        placeholder="08xxxxxxxxxx"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 transition">
                    @error('no_hp')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap</label>
                    <textarea name="alamat" rows="3" placeholder="Masukkan alamat lengkap tempat tinggal..."
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 transition">{{ old('alamat', $user->pengurus->alamat ?? '') }}</textarea>
                    @error('alamat')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-gray-100">
                <button type="submit"
                    class="w-full sm:w-auto bg-yellow-500 hover:bg-yellow-600 text-white font-medium py-2.5 px-8 rounded-lg shadow-sm transition text-sm flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
@endsection
