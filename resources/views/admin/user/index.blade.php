@extends('layouts.admin')
@section('title', 'Manajemen User')

@section('content')

    {{-- Filter & Search --}}
    <div class="bg-white rounded-lg shadow p-4 mb-4 sm:mb-6">
        {{-- Tambahkan ID pada Form agar mudah dipanggil JS --}}
        <form id="form-filter" method="GET" action="{{ route('admin.user.index') }}"
            class="flex flex-col sm:flex-row gap-3 sm:gap-4 sm:items-end">
            <div class="w-full sm:flex-1">
                <label class="block text-xs font-medium text-gray-600 mb-1">Cari Username / Nama Lengkap</label>
                <input type="text" name="search" id="input-search" value="{{ $search }}"
                    placeholder="Ketik username atau nama..."
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="w-full sm:w-48">
                <label class="block text-xs font-medium text-gray-600 mb-1">Role</label>
                {{-- Tambahkan ID pada select role --}}
                <select name="role" id="select-role"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Role</option>
                    <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="ketua" {{ $role === 'ketua' ? 'selected' : '' }}>Ketua</option>
                    <option value="pelanggan" {{ $role === 'pelanggan' ? 'selected' : '' }}>Pelanggan</option>
                </select>
            </div>
            <div class="flex gap-2 w-full sm:w-auto mt-2 sm:mt-0">
                {{-- Tombol "Cari" Dihapus karena sudah otomatis --}}
                <a href="{{ route('admin.user.index') }}"
                    class="flex-1 sm:flex-none text-center bg-gray-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-600 transition">
                    Reset Filter
                </a>
            </div>
        </form>
    </div>

    {{-- Tabel --}}
    <div class="bg-white rounded-lg shadow">
        <div class="p-4 sm:p-6 border-b">
            <h2 class="text-base sm:text-lg font-semibold text-gray-700">Daftar User</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap">No</th>
                        <th class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap">Username</th>
                        <th class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap">Nama</th>
                        <th class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap">Role</th>
                        <th class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap">Status</th>
                        <th class="px-4 sm:px-6 py-3 sm:py-4 text-center whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $index => $user)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 sm:px-6 py-3 sm:py-4 text-gray-500 whitespace-nowrap">
                                {{ ($users->currentPage() - 1) * $users->perPage() + $index + 1 }}
                            </td>
                            <td class="px-4 sm:px-6 py-3 sm:py-4 font-mono font-semibold text-blue-600 whitespace-nowrap">
                                {{ $user->username }}
                                @if ($user->id === auth()->id())
                                    <span
                                        class="inline-block mt-1 sm:mt-0 text-[10px] sm:text-xs bg-blue-100 text-blue-600 px-2 py-0.5 rounded-full sm:ml-1">Anda</span>
                                @endif
                            </td>
                            <td class="px-4 sm:px-6 py-3 sm:py-4 text-gray-800 whitespace-nowrap">
                                @if ($user->role === 'pelanggan' && $user->pelanggan)
                                    {{ $user->nama_lengkap }}
                                @elseif(in_array($user->role, ['admin', 'ketua']) && $user->pengurus)
                                    {{ $user->nama_lengkap }}
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                @if ($user->role === 'admin')
                                    <span
                                        class="px-2 sm:px-3 py-1 bg-red-100 text-red-700 rounded-full text-[10px] sm:text-xs font-medium">Admin</span>
                                @elseif($user->role === 'ketua')
                                    <span
                                        class="px-2 sm:px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-[10px] sm:text-xs font-medium">Ketua</span>
                                @else
                                    <span
                                        class="px-2 sm:px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-[10px] sm:text-xs font-medium">Pelanggan</span>
                                @endif
                            </td>
                            <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                @if ($user->is_active)
                                    <span
                                        class="px-2 sm:px-3 py-1 bg-green-100 text-green-700 rounded-full text-[10px] sm:text-xs font-medium">Aktif</span>
                                @else
                                    <span
                                        class="px-2 sm:px-3 py-1 bg-red-100 text-red-700 rounded-full text-[10px] sm:text-xs font-medium">Non-aktif</span>
                                @endif
                            </td>
                            <td class="px-4 sm:px-6 py-3 sm:py-4">
                                <div class="flex flex-col sm:flex-row gap-2 justify-center items-center">
                                    <a href="{{ route('admin.user.show', $user) }}"
                                        class="w-full sm:w-auto px-3 py-1.5 bg-blue-600 text-white rounded text-xs font-medium hover:bg-blue-700 transition text-center whitespace-nowrap">
                                        Detail
                                    </a>

                                    @if (in_array($user->role, ['admin', 'ketua']))
                                        <a href="{{ route('admin.user.edit', $user) }}"
                                            class="w-full sm:w-auto px-3 py-1.5 bg-yellow-500 text-white rounded text-xs font-medium hover:bg-yellow-600 transition text-center whitespace-nowrap">
                                            Edit
                                        </a>
                                    @endif

                                    @if ($user->id !== auth()->id())
                                        <form action="{{ route('admin.user.toggle-status', $user) }}" method="POST"
                                            class="w-full sm:w-auto m-0">
                                            @csrf
                                            <button type="submit"
                                                class="w-full sm:w-auto px-3 py-1.5 {{ $user->is_active ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-green-600 hover:bg-green-700' }} text-white rounded text-xs font-medium transition whitespace-nowrap">
                                                {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 sm:px-6 py-8 sm:py-10 text-center text-gray-400">
                                Tidak ada user ditemukan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="p-4 border-t">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    {{-- Script untuk Auto Search (Debounce) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('form-filter');
            const inputSearch = document.getElementById('input-search');
            const selectRole = document.getElementById('select-role');

            let typingTimer;
            const doneTypingInterval = 500; // Jeda 0.5 detik

            // Otomatis submit setelah selesai mengetik (Debounce)
            inputSearch.addEventListener('input', function() {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(function() {
                    form.submit();
                }, doneTypingInterval);
            });

            // Otomatis submit saat ganti dropdown Role
            selectRole.addEventListener('change', function() {
                form.submit();
            });

            // (Opsional) Mengembalikan fokus kursor ke ujung teks input setelah halaman ter-reload
            if (inputSearch.value) {
                const valLength = inputSearch.value.length;
                inputSearch.focus();
                inputSearch.setSelectionRange(valLength, valLength);
            }
        });
    </script>
@endsection
