<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} - @yield('title', 'Dashboard')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 font-sans">

    <div class="flex h-screen overflow-hidden">

        {{-- OVERLAY (mobile) --}}
        <div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-20 hidden lg:hidden"
            onclick="closeSidebar()">
        </div>

        {{-- SIDEBAR --}}
        <aside id="sidebar"
            class="fixed lg:static inset-y-0 left-0 z-30 w-full lg:w-64 bg-blue-900 text-white flex flex-col flex-shrink-0 transition-transform duration-300 -translate-x-full lg:translate-x-0">
            {{-- Logo --}}
            <div class="flex items-center justify-between h-16 bg-blue-950 px-4">
                <span class="text-white font-bold text-lg leading-tight">
                    💧 KP-SPAMS<br>
                    <span class="text-xs font-normal text-blue-200">Tirta Wening</span>
                </span>
                {{-- Close button (mobile only) --}}
                <button onclick="closeSidebar()" class="lg:hidden text-blue-200 hover:text-white p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- User Info --}}
            <div class="px-4 py-3 bg-blue-800 border-b border-blue-700">
                <p class="text-sm font-semibold text-white truncate">{{ Auth::user()->nama_lengkap }}</p>
                <p class="text-xs text-blue-200 capitalize">{{ Auth::user()->role }}</p>
            </div>

            {{-- Navigation Menu --}}
            <nav class="flex-1 overflow-y-auto py-4">
                @if (Auth::user()->isAdmin())
                    @include('layouts.partials.sidebar-admin')
                @elseif(Auth::user()->isKetua())
                    @include('layouts.partials.sidebar-ketua')
                @endif
            </nav>

            {{-- Logout --}}
            <div class="p-4 border-t border-blue-700">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center px-3 py-2 text-sm text-blue-200 hover:bg-blue-700 hover:text-white rounded-lg transition">
                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Keluar
                    </button>
                </form>
            </div>
        </aside>

        {{-- MAIN CONTENT --}}
        <div class="flex-1 flex flex-col overflow-hidden min-w-0">

            {{-- Top Header --}}
            <header
                class="bg-white shadow-sm h-16 flex items-center justify-between px-4 lg:px-6 flex-shrink-0 z-20 relative">
                <div class="flex items-center">
                    {{-- Burger Button --}}
                    <button id="burgerBtn" onclick="toggleSidebar()"
                        class="text-gray-500 hover:text-gray-700 mr-3 p-1 rounded-lg hover:bg-gray-100 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <h1 class="text-base lg:text-lg font-semibold text-gray-700 truncate">
                        @yield('title', 'Dashboard')
                    </h1>
                </div>

                {{-- Bagian Kanan Header --}}
                <div class="flex items-center gap-2 sm:gap-4">
                    <div class="text-xs lg:text-sm text-gray-500 hidden sm:block pr-2 sm:pr-4 border-r border-gray-200">
                        {{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM Y') }}
                    </div>

                    {{-- Lonceng Notifikasi Admin --}}
                    @if (Auth::user()->isAdmin())
                        <div class="relative">
                            <button onclick="toggleNotifAdmin()" id="notifBtnAdmin"
                                class="relative p-2 text-gray-500 hover:bg-gray-100 hover:text-blue-600 rounded-full transition focus:outline-none">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>

                                @if (auth()->user()->unreadNotifications->count() > 0)
                                    <span
                                        class="absolute top-1 right-1 sm:top-1.5 sm:right-1.5 flex items-center justify-center w-4 h-4 text-[9px] font-bold text-white bg-red-500 border-2 border-white rounded-full">
                                        {{ auth()->user()->unreadNotifications->count() }}
                                    </span>
                                @endif
                            </button>

                            <div id="notifDropdownAdmin"
                                class="hidden absolute right-0 mt-2 w-72 sm:w-80 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden z-50">
                                <div class="p-3 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
                                    <h3 class="text-sm font-bold text-gray-700">Notifikasi Admin</h3>
                                    @if (auth()->user()->unreadNotifications->count() > 0)
                                        <form action="{{ route('admin.notif.markAllRead') }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                class="text-[10px] text-blue-600 hover:text-blue-800 font-medium">Tandai
                                                dibaca</button>
                                        </form>
                                    @endif
                                </div>
                                <div class="max-h-80 overflow-y-auto">
                                    @forelse(auth()->user()->unreadNotifications as $notif)
                                        @php
                                            $urlNotif = $notif->data['url'] ?? '#';

                                            // Trik Pintar: Tambahkan parameter notif_id ke URL agar Controller bisa menandainya sebagai terbaca
                                            if (
                                                isset($notif->data['tipe']) &&
                                                $notif->data['tipe'] === 'pembayaran_baru' &&
                                                isset($notif->data['tagihan_id'])
                                            ) {
                                                $urlNotif = route('admin.pembayaran.show', [
                                                    'pembayaran' => $notif->data['tagihan_id'],
                                                    'notif_id' => $notif->id,
                                                ]);
                                            }
                                        @endphp

                                        <a href="{{ $urlNotif }}"
                                            class="block p-4 border-b border-gray-50 hover:bg-blue-50 transition">
                                            <p class="text-xs font-semibold text-gray-800">{{ $notif->data['title'] }}</p>
                                            <p class="text-[10px] text-gray-500 mt-1 line-clamp-2">
                                                {{ $notif->data['message'] }}</p>
                                            <p class="text-[9px] text-gray-400 mt-2">
                                                {{ $notif->created_at->diffForHumans() }}</p>
                                        </a>
                                    @empty
                                        <div class="p-6 text-center text-gray-400">
                                            <p class="text-xs">Belum ada notifikasi baru</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </header>

            {{-- Page Content --}}
            <main class="flex-1 overflow-y-auto p-4 lg:p-6">
                @yield('content')
            </main>
        </div>
    </div>

    {{-- Sidebar Toggle Script --}}
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const isOpen = !sidebar.classList.contains('-translate-x-full');

            if (window.innerWidth >= 1024) {
                // Desktop — toggle collapse
                sidebar.classList.toggle('lg:static');
                sidebar.classList.toggle('w-64');
                sidebar.classList.toggle('w-0');
                sidebar.classList.toggle('overflow-hidden');
            } else {
                // Mobile — slide in/out
                if (isOpen) {
                    closeSidebar();
                } else {
                    openSidebar();
                }
            }
        }

        function openSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        // Handle resize
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1024) {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('sidebarOverlay');
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        });

        // Script untuk Dropdown Lonceng Notifikasi Admin
        function toggleNotifAdmin() {
            document.getElementById('notifDropdownAdmin').classList.toggle('hidden');
        }
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('notifDropdownAdmin');
            const btn = document.getElementById('notifBtnAdmin');
            if (dropdown && btn && !dropdown.contains(event.target) && !btn.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });
    </script>

    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                timer: 3000,
                showConfirmButton: false
            });
        @endif

        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: "{{ session('error') }}",
            });
        @endif

        // Konfirmasi hapus
        document.querySelectorAll('.btn-hapus').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const form = this.closest('form');
                Swal.fire({
                    title: 'Yakin ingin menghapus?',
                    text: 'Data yang dihapus tidak dapat dikembalikan!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>

    @stack('scripts')

</body>

</html>
