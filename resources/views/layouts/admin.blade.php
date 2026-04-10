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
                <p class="text-sm font-semibold text-white">{{ Auth::user()->username }}</p>
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
            <header class="bg-white shadow-sm h-16 flex items-center justify-between px-4 lg:px-6 flex-shrink-0">
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
                <div class="text-xs lg:text-sm text-gray-500 hidden sm:block">
                    {{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM Y') }}
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
