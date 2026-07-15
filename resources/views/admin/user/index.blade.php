@extends('layouts.admin')
@section('title', 'Manajemen User')

@section('content')

    {{-- Filter & Search --}}
    <div class="bg-white rounded-lg shadow p-4 mb-4 sm:mb-6 flex flex-col sm:flex-row gap-4 items-end">
        <form id="form-filter" method="GET" action="{{ route('admin.user.index') }}"
            class="flex flex-col sm:flex-row gap-3 sm:gap-4 sm:items-end w-full">
            <div class="w-full sm:flex-1">
                <label class="block text-xs font-medium text-gray-600 mb-1">Cari Username / Nama Lengkap</label>
                <input type="text" name="search" id="input-search" value="{{ $search }}"
                    placeholder="Ketik username atau nama..."
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex gap-2 w-full sm:w-auto mt-2 sm:mt-0">
                <a href="{{ route('admin.user.index') }}"
                    class="flex-1 sm:flex-none text-center bg-gray-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-600 transition">
                    Reset Filter
                </a>
            </div>
        </form>

        <a href="{{ route('admin.user.create') }}"
            class="w-full sm:w-auto bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition inline-flex justify-center items-center gap-2 whitespace-nowrap">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah User
        </a>
    </div>

    {{-- Tabs Navigation --}}
    <div class="mb-6 border-b border-gray-200">
        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="userTabs" role="tablist">
            <li class="mr-2" role="presentation">
                <button class="inline-block p-4 border-b-2 rounded-t-lg text-blue-600 border-blue-600 font-bold" id="admin-tab" onclick="switchTab('admin')">
                    Admin ({{ $admins->count() }})
                </button>
            </li>
            <li class="mr-2" role="presentation">
                <button class="inline-block p-4 border-b-2 rounded-t-lg border-transparent text-gray-500 hover:text-gray-600 hover:border-gray-300" id="ketua-tab" onclick="switchTab('ketua')">
                    Ketua ({{ $ketuas->count() }})
                </button>
            </li>
            <li class="mr-2" role="presentation">
                <button class="inline-block p-4 border-b-2 rounded-t-lg border-transparent text-gray-500 hover:text-gray-600 hover:border-gray-300" id="pelanggan-tab" onclick="switchTab('pelanggan')">
                    Pelanggan ({{ $pelanggans->total() }})
                </button>
            </li>
        </ul>
    </div>

    {{-- Tab Contents --}}
    <div id="tab-contents">
        
        {{-- Tab Admin --}}
        <div id="content-admin" class="tab-content bg-white rounded-lg shadow overflow-hidden">
            <div class="p-4 sm:p-6 border-b">
                <h2 class="text-base sm:text-lg font-semibold text-gray-700">Daftar Administrator</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-600 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="px-4 sm:px-6 py-3 sm:py-4">No</th>
                            <th class="px-4 sm:px-6 py-3 sm:py-4">Username</th>
                            <th class="px-4 sm:px-6 py-3 sm:py-4">Nama Lengkap</th>
                            <th class="px-4 sm:px-6 py-3 sm:py-4">No. HP</th>
                            <th class="px-4 sm:px-6 py-3 sm:py-4">Alamat</th>
                            <th class="px-4 sm:px-6 py-3 sm:py-4">Status</th>
                            <th class="px-4 sm:px-6 py-3 sm:py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($admins as $index => $user)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 sm:px-6 py-3 sm:py-4 text-gray-500">{{ $index + 1 }}</td>
                                <td class="px-4 sm:px-6 py-3 sm:py-4 font-mono font-semibold text-blue-600">
                                    {{ $user->username }}
                                    @if ($user->id === auth()->id())
                                        <span class="text-[10px] bg-blue-100 text-blue-600 px-2 py-0.5 rounded-full ml-1 font-bold">Anda</span>
                                    @endif
                                </td>
                                <td class="px-4 sm:px-6 py-3 sm:py-4 text-gray-800">{{ $user->nama_lengkap }}</td>
                                <td class="px-4 sm:px-6 py-3 sm:py-4 text-gray-600 font-mono">{{ $user->no_hp ?? '-' }}</td>
                                <td class="px-4 sm:px-6 py-3 sm:py-4 text-gray-600">{{ $user->alamat ?? '-' }}</td>
                                <td class="px-4 sm:px-6 py-3 sm:py-4">
                                    <span class="px-2.5 py-0.5 {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }} rounded-full text-xs font-semibold">
                                        {{ $user->is_active ? 'Aktif' : 'Non-aktif' }}
                                    </span>
                                </td>
                                <td class="px-4 sm:px-6 py-3 sm:py-4">
                                    <div class="flex gap-2 justify-center">
                                        <a href="{{ route('admin.user.show', $user) }}" class="px-3 py-1.5 bg-blue-600 text-white rounded text-xs font-medium hover:bg-blue-700 transition">Detail</a>
                                        <a href="{{ route('admin.user.edit', $user) }}" class="px-3 py-1.5 bg-yellow-500 text-white rounded text-xs font-medium hover:bg-yellow-600 transition">Edit</a>
                                        @if ($user->id !== auth()->id())
                                            <form action="{{ route('admin.user.toggle-status', $user) }}" method="POST" class="m-0">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 {{ $user->is_active ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-green-600 hover:bg-green-700' }} text-white rounded text-xs font-medium transition">
                                                    {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 sm:px-6 py-8 text-center text-gray-400">Tidak ada admin ditemukan</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Tab Ketua --}}
        <div id="content-ketua" class="tab-content bg-white rounded-lg shadow overflow-hidden hidden">
            <div class="p-4 sm:p-6 border-b">
                <h2 class="text-base sm:text-lg font-semibold text-gray-700">Daftar Pengurus / Ketua</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-600 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="px-4 sm:px-6 py-3 sm:py-4">No</th>
                            <th class="px-4 sm:px-6 py-3 sm:py-4">Username</th>
                            <th class="px-4 sm:px-6 py-3 sm:py-4">Nama Lengkap</th>
                            <th class="px-4 sm:px-6 py-3 sm:py-4">Jabatan</th>
                            <th class="px-4 sm:px-6 py-3 sm:py-4">No. HP</th>
                            <th class="px-4 sm:px-6 py-3 sm:py-4">Alamat</th>
                            <th class="px-4 sm:px-6 py-3 sm:py-4">Status</th>
                            <th class="px-4 sm:px-6 py-3 sm:py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($ketuas as $index => $user)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 sm:px-6 py-3 sm:py-4 text-gray-500">{{ $index + 1 }}</td>
                                <td class="px-4 sm:px-6 py-3 sm:py-4 font-mono font-semibold text-blue-600">{{ $user->username }}</td>
                                <td class="px-4 sm:px-6 py-3 sm:py-4 text-gray-800">{{ $user->nama_lengkap }}</td>
                                <td class="px-4 sm:px-6 py-3 sm:py-4 text-gray-700 font-semibold">{{ $user->pengurus->jabatan ?? '-' }}</td>
                                <td class="px-4 sm:px-6 py-3 sm:py-4 text-gray-600 font-mono">{{ $user->no_hp ?? '-' }}</td>
                                <td class="px-4 sm:px-6 py-3 sm:py-4 text-gray-600">{{ $user->alamat ?? '-' }}</td>
                                <td class="px-4 sm:px-6 py-3 sm:py-4">
                                    <span class="px-2.5 py-0.5 {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }} rounded-full text-xs font-semibold">
                                        {{ $user->is_active ? 'Aktif' : 'Non-aktif' }}
                                    </span>
                                </td>
                                <td class="px-4 sm:px-6 py-3 sm:py-4">
                                    <div class="flex gap-2 justify-center">
                                        <a href="{{ route('admin.user.show', $user) }}" class="px-3 py-1.5 bg-blue-600 text-white rounded text-xs font-medium hover:bg-blue-700 transition">Detail</a>
                                        <a href="{{ route('admin.user.edit', $user) }}" class="px-3 py-1.5 bg-yellow-500 text-white rounded text-xs font-medium hover:bg-yellow-600 transition">Edit</a>
                                        <form action="{{ route('admin.user.toggle-status', $user) }}" method="POST" class="m-0">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 {{ $user->is_active ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-green-600 hover:bg-green-700' }} text-white rounded text-xs font-medium transition">
                                                {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 sm:px-6 py-8 text-center text-gray-400">Tidak ada pengurus/ketua ditemukan</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Tab Pelanggan --}}
        <div id="content-pelanggan" class="tab-content bg-white rounded-lg shadow overflow-hidden hidden">
            <div class="p-4 sm:p-6 border-b">
                <h2 class="text-base sm:text-lg font-semibold text-gray-700">Daftar Pelanggan / Warga</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-600 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="px-4 sm:px-6 py-3 sm:py-4">No</th>
                            <th class="px-4 sm:px-6 py-3 sm:py-4">Kode Pelanggan</th>
                            <th class="px-4 sm:px-6 py-3 sm:py-4">Nama Pelanggan</th>
                            <th class="px-4 sm:px-6 py-3 sm:py-4">NIK</th>
                            <th class="px-4 sm:px-6 py-3 sm:py-4">No. HP</th>
                            <th class="px-4 sm:px-6 py-3 sm:py-4">Saldo (Deposit)</th>
                            <th class="px-4 sm:px-6 py-3 sm:py-4">Status</th>
                            <th class="px-4 sm:px-6 py-3 sm:py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($pelanggans as $index => $user)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 sm:px-6 py-3 sm:py-4 text-gray-500">
                                    {{ ($pelanggans->currentPage() - 1) * $pelanggans->perPage() + $index + 1 }}
                                </td>
                                <td class="px-4 sm:px-6 py-3 sm:py-4 font-mono font-semibold text-blue-600">
                                    {{ $user->username }}
                                </td>
                                <td class="px-4 sm:px-6 py-3 sm:py-4 text-gray-800">{{ $user->nama_lengkap }}</td>
                                <td class="px-4 sm:px-6 py-3 sm:py-4 text-gray-600 font-mono">{{ $user->pelanggan->nik ?? '-' }}</td>
                                <td class="px-4 sm:px-6 py-3 sm:py-4 text-gray-600 font-mono">{{ $user->no_hp ?? '-' }}</td>
                                <td class="px-4 sm:px-6 py-3 sm:py-4 text-green-600 font-bold font-mono">
                                    Rp {{ number_format($user->pelanggan->saldo ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="px-4 sm:px-6 py-3 sm:py-4">
                                    <span class="px-2.5 py-0.5 {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }} rounded-full text-xs font-semibold">
                                        {{ $user->is_active ? 'Aktif' : 'Non-aktif' }}
                                    </span>
                                </td>
                                <td class="px-4 sm:px-6 py-3 sm:py-4">
                                    <div class="flex gap-2 justify-center">
                                        <a href="{{ route('admin.user.show', $user) }}" class="px-3 py-1.5 bg-blue-600 text-white rounded text-xs font-medium hover:bg-blue-700 transition">Detail</a>
                                        <form action="{{ route('admin.user.toggle-status', $user) }}" method="POST" class="m-0">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 {{ $user->is_active ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-green-600 hover:bg-green-700' }} text-white rounded text-xs font-medium transition">
                                                {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 sm:px-6 py-8 text-center text-gray-400">Tidak ada pelanggan ditemukan</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($pelanggans->hasPages())
                <div class="p-4 border-t">
                    {{ $pelanggans->links() }}
                </div>
            @endif
        </div>

    </div>

    {{-- Script untuk Auto Search (Debounce) dan Tab Management --}}
    <script>
        function switchTab(tabId) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
            // Show active tab content
            document.getElementById('content-' + tabId).classList.remove('hidden');

            // Reset tab button styles
            document.querySelectorAll('#userTabs button').forEach(btn => {
                btn.className = "inline-block p-4 border-b-2 rounded-t-lg border-transparent text-gray-500 hover:text-gray-600 hover:border-gray-300";
            });

            // Set active tab button style
            const activeBtn = document.getElementById(tabId + '-tab');
            activeBtn.className = "inline-block p-4 border-b-2 rounded-t-lg text-blue-600 border-blue-600 font-bold";

            // Update URL search param tab without reload
            const url = new URL(window.location.href);
            url.searchParams.set('tab', tabId);
            window.history.replaceState({}, '', url.toString());
        }

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('form-filter');
            const inputSearch = document.getElementById('input-search');

            let typingTimer;
            const doneTypingInterval = 500; // Jeda 0.5 detik

            // Otomatis submit setelah selesai mengetik (Debounce)
            inputSearch.addEventListener('input', function() {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(function() {
                    form.submit();
                }, doneTypingInterval);
            });

            // Restore Cursor Position
            if (inputSearch.value) {
                const valLength = inputSearch.value.length;
                inputSearch.focus();
                inputSearch.setSelectionRange(valLength, valLength);
            }

            // Detect Active Tab on Load
            const urlParams = new URLSearchParams(window.location.search);
            let activeTab = urlParams.get('tab') || 'admin';
            
            // If page parameter exists (indicating pelanggan pagination was clicked), focus on Pelanggan tab
            if (urlParams.has('page')) {
                activeTab = 'pelanggan';
            }

            switchTab(activeTab);
        });
    </script>
@endsection
