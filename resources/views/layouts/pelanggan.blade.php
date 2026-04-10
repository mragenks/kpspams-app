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
        <header class="bg-blue-900 text-white shadow-lg">
            <div class="max-w-4xl mx-auto px-4 py-4 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">💧</span>
                    <div>
                        <p class="font-bold text-lg leading-tight">KP-SPAMS Tirta Wening</p>
                        <p class="text-xs text-blue-200">Portal Pelanggan</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-right">
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
    </script>

</body>

</html>
