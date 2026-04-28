<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - @yield('title', 'Portal Pelanggan')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 font-sans">

    <div class="min-h-screen">

        {{-- Header --}}
        <header class="bg-blue-900 text-white shadow-lg relative z-20">
            <div class="max-w-4xl mx-auto px-4 py-4 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">💧</span>
                    <div>
                        <p class="font-bold text-lg leading-tight">KP-SPAMS Tirta Wening</p>
                        <p class="text-xs text-blue-200">Portal Pelanggan</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 sm:gap-4">

                    {{-- Lonceng Notifikasi Pelanggan --}}
                    <div class="relative">
                        <button onclick="toggleNotif()" id="notifBtn"
                            class="relative p-2 text-blue-100 hover:bg-blue-800 hover:text-white rounded-full transition focus:outline-none">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>

                            {{-- Badge Angka Merah --}}
                            @if (auth()->user()->unreadNotifications->count() > 0)
                                <span
                                    class="absolute top-1 right-1 sm:top-1.5 sm:right-1.5 flex items-center justify-center w-4 h-4 text-[9px] font-bold text-white bg-red-500 border-2 border-blue-900 rounded-full">
                                    {{ auth()->user()->unreadNotifications->count() }}
                                </span>
                            @endif
                        </button>

                        {{-- Dropdown Isi Notifikasi --}}
                        <div id="notifDropdown"
                            class="hidden absolute right-0 mt-2 w-72 sm:w-80 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden z-50">
                            <div class="p-3 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
                                <h3 class="text-sm font-bold text-gray-700">Notifikasi Anda</h3>
                                @if (auth()->user()->unreadNotifications->count() > 0)
                                    <form action="{{ route('pelanggan.notif.markAllRead') }}" method="POST">
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

                                        // Trik Pintar: Cegat notif 'tagihan_baru' agar mengarah ke Surat Tagihan (Berlaku untuk notif lama & baru)
                                        if (
                                            $notif->data['tipe'] === 'tagihan_baru' &&
                                            isset($notif->data['tagihan_id'])
                                        ) {
                                            $urlNotif = route('pelanggan.tagihan.surat', [
                                                'id' => $notif->data['tagihan_id'],
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
                                        <svg class="w-8 h-8 mx-auto mb-2 opacity-50" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                                            </path>
                                        </svg>
                                        <p class="text-xs">Belum ada notifikasi baru</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-semibold">{{ Auth::user()->username }}</p>
                        <p class="text-xs text-blue-200">Pelanggan</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="bg-blue-700 hover:bg-blue-600 text-white text-sm px-3 py-1.5 rounded-lg transition">
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </header>

        {{-- Content --}}
        <main class="max-w-4xl mx-auto px-4 py-6">
            @yield('content')
        </main>

        {{-- Footer --}}
        <footer class="text-center text-xs text-gray-400 py-6">
            KP-SPAMS Tirta Wening — Desa Singodutan, Kab. Wonogiri
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                confirmButtonColor: '#1e3a8a'
            });
        @endif

        @if ($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Oops! Upload Gagal',
                html: `{!! implode('<br>', $errors->all()) !!}`,
                confirmButtonColor: '#1e3a8a'
            });
        @endif

        @if ($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Oops! Upload Gagal',
                html: `{!! implode('<br>', $errors->all()) !!}`, 
                confirmButtonColor: '#1e3a8a'
            });
        @endif

        function toggleNotif() {
            document.getElementById('notifDropdown').classList.toggle('hidden');
        }

        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('notifDropdown');
            const btn = document.getElementById('notifBtn');
            if (dropdown && btn && !dropdown.contains(event.target) && !btn.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });
    </script>

</body>

</html>
