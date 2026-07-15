@extends('layouts.admin')
@section('title', 'Detail User')

@section('content')
    <div class="max-w-2xl mx-auto space-y-4 sm:space-y-6">

        {{-- Info User --}}
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <div
                class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 sm:mb-6 border-b pb-4 sm:border-none sm:pb-0 gap-3 sm:gap-0">
                <h2 class="text-base sm:text-lg font-semibold text-gray-700">Detail User</h2>
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    {{-- TOMBOL EDIT KHUSUS ADMIN, KETUA, & PENCATAT DI HALAMAN SHOW --}}
                    @if (in_array($user->role, ['admin', 'ketua', 'pencatat']))
                        <a href="{{ route('admin.user.edit', $user) }}"
                            class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1.5 rounded text-xs sm:text-sm font-medium transition flex-1 sm:flex-none text-center">
                            Edit User
                        </a>
                    @endif
                    <a href="{{ route('admin.user.index') }}"
                        class="text-xs sm:text-sm text-blue-600 hover:underline flex-1 sm:flex-none text-right">
                        ← Kembali
                    </a>
                </div>
            </div>

            <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <p class="text-[10px] sm:text-xs text-gray-400 uppercase">Username</p>
                        <p class="font-mono font-semibold text-blue-600 text-base sm:text-lg">{{ $user->username }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] sm:text-xs text-gray-400 uppercase mb-1">Role</p>
                        @if ($user->role === 'admin')
                            <span
                                class="px-2 sm:px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs sm:text-sm font-medium">Admin</span>
                        @elseif($user->role === 'ketua')
                            <span
                                class="px-2 sm:px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-xs sm:text-sm font-medium">Ketua</span>
                        @elseif($user->role === 'pencatat')
                            <span
                                class="px-2 sm:px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs sm:text-sm font-medium">Pencatat Meter</span>
                        @else
                            <span
                                class="px-2 sm:px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs sm:text-sm font-medium">Pelanggan</span>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <p class="text-[10px] sm:text-xs text-gray-400 uppercase mb-1">Status</p>
                        @if ($user->is_active)
                            <span
                                class="px-2 sm:px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs sm:text-sm font-medium">Aktif</span>
                        @else
                            <span
                                class="px-2 sm:px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs sm:text-sm font-medium">Non-aktif</span>
                        @endif
                    </div>
                    <div>
                        <p class="text-[10px] sm:text-xs text-gray-400 uppercase">Dibuat</p>
                        <p class="text-sm sm:text-base">{{ $user->created_at->isoFormat('D MMMM Y') }}</p>
                    </div>
                </div>

                {{-- Info tambahan sesuai role --}}
                @if ($user->isPelanggan() && $user->pelanggan)
                    <div class="border-t pt-4 mt-4">
                        <p class="text-[10px] sm:text-xs text-gray-400 uppercase mb-2">Info Pelanggan</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <p class="text-[10px] sm:text-xs text-gray-400">Nama</p>
                            <p class="font-medium text-sm sm:text-base">{{ $user->nama_lengkap }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] sm:text-xs text-gray-400">Kode Pelanggan</p>
                                <p class="font-medium text-blue-600 text-sm sm:text-base">
                                    {{ $user->pelanggan->kode_pelanggan }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] sm:text-xs text-gray-400">No. HP</p>
                            <p class="text-sm sm:text-base">{{ $user->no_hp ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] sm:text-xs text-gray-400">Alamat</p>
                            <p class="text-sm sm:text-base">{{ $user->alamat ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($user->pengurus)
                    <div class="border-t pt-4 mt-4">
                        <p class="text-[10px] sm:text-xs text-gray-400 uppercase mb-2">Info Pengurus</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <p class="text-[10px] sm:text-xs text-gray-400">Nama</p>
                            <p class="font-medium text-sm sm:text-base">{{ $user->nama_lengkap }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] sm:text-xs text-gray-400">Jabatan</p>
                                <p class="capitalize text-sm sm:text-base">{{ $user->pengurus->jabatan }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Reset Password --}}
        @if ($user->id !== auth()->id())
            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <h3 class="text-base font-semibold text-gray-700 mb-1">Reset Password</h3>
                <p class="text-xs sm:text-sm text-gray-500 mb-4 sm:mb-6">
                    Gunakan fitur ini untuk mereset password user jika mereka lupa password.
                </p>

                <form action="{{ route('admin.user.reset-password', $user) }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">
                            Password Baru <span class="text-red-500">*</span>
                        </label>
                        <input type="password" name="password_baru" placeholder="Minimal 6 karakter"
                            class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition {{ $errors->has('password_baru') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                        @error('password_baru')
                            <p class="text-red-500 text-[10px] sm:text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4 sm:mb-6">
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">
                            Konfirmasi Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" name="password_baru_confirmation" placeholder="Ulangi password baru"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    </div>

                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
                        <p class="text-[10px] sm:text-xs text-yellow-700">
                            ⚠️ Password lama akan langsung diganti. Pastikan user sudah diberitahu password barunya.
                        </p>
                    </div>

                    <button type="submit"
                        class="w-full sm:w-auto bg-red-600 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-red-700 transition">
                        Reset Password
                    </button>
                </form>
            </div>
        @else
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <p class="text-xs sm:text-sm text-blue-700">
                    💡 Ini adalah akun Anda sendiri. Reset password tidak tersedia untuk akun sendiri.
                </p>
            </div>
        @endif

    </div>
@endsection
