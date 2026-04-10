@extends('layouts.pelanggan')
@section('title', 'Dashboard')

@section('content')

    {{-- Greeting --}}
    <div class="bg-blue-900 text-white rounded-2xl p-6 mb-6 shadow">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-blue-200 text-sm mb-1">Selamat datang,</p>
                <h1 class="text-2xl font-bold">{{ $pelanggan->nama_pelanggan }}</h1>
                <p class="text-blue-300 text-sm mt-1">
                    {{ $pelanggan->kode_pelanggan }} •
                    {{ $pelanggan->alamat ?? 'Desa Singodutan' }}
                </p>
            </div>
            <div class="text-right">
                <span
                    class="px-3 py-1 {{ $pelanggan->status === 'aktif' ? 'bg-green-500' : 'bg-red-500' }} rounded-full text-xs font-medium">
                    {{ ucfirst($pelanggan->status) }}
                </span>
            </div>
        </div>
    </div>

    {{-- Tagihan Aktif --}}
    @if ($tagihanAktif)
        <div
            class="bg-white rounded-xl shadow p-6 mb-6 border-l-4 {{ $tagihanAktif->status_bayar === 'mencicil' ? 'border-yellow-500' : 'border-red-500' }}">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-sm text-gray-500">Tagihan Aktif</p>
                    <h2 class="text-lg font-bold text-gray-800">
                        {{ \Carbon\Carbon::create()->month($tagihanAktif->bulan_periode)->isoFormat('MMMM') }}
                        {{ $tagihanAktif->tahun_periode }}
                    </h2>
                </div>
                @if ($tagihanAktif->status_bayar === 'mencicil')
                    <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm font-medium">Mencicil</span>
                @else
                    <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm font-medium">Belum Lunas</span>
                @endif
            </div>

            <div class="grid grid-cols-3 gap-4 mb-4">
                <div class="bg-gray-50 rounded-lg p-3 text-center">
                    <p class="text-xs text-gray-400 uppercase mb-1">Total Tagihan</p>
                    <p class="font-bold text-gray-800">
                        Rp {{ number_format($tagihanAktif->total_tagihan, 0, ',', '.') }}
                    </p>
                </div>
                <div class="bg-green-50 rounded-lg p-3 text-center">
                    <p class="text-xs text-gray-400 uppercase mb-1">Sudah Dibayar</p>
                    <p class="font-bold text-green-600">
                        Rp {{ number_format($tagihanAktif->total_terbayar, 0, ',', '.') }}
                    </p>
                </div>
                <div class="bg-red-50 rounded-lg p-3 text-center">
                    <p class="text-xs text-gray-400 uppercase mb-1">Sisa Tagihan</p>
                    <p class="font-bold text-red-600">
                        Rp {{ number_format($tagihanAktif->sisa_tagihan, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            @if ($tagihanAktif->pencatatanMeter)
                <div class="bg-blue-50 rounded-lg p-3">
                    <span class="text-sm text-blue-700">
                        💧 Pemakaian:
                        <strong>{{ number_format($tagihanAktif->pencatatanMeter->volume_m3, 2) }} m³</strong>
                        ({{ number_format($tagihanAktif->pencatatanMeter->meter_awal, 2) }} →
                        {{ number_format($tagihanAktif->pencatatanMeter->meter_akhir, 2) }})
                    </span>
                </div>
            @endif

            <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                <p class="text-sm text-yellow-700">
                    ⚠️ Harap segera melunasi tagihan. Hubungi pengurus KP-SPAMS untuk pembayaran.
                </p>
            </div>
        </div>
    @else
        <div class="bg-white rounded-xl shadow p-6 mb-6 border-l-4 border-green-500">
            <div class="flex items-center gap-3">
                <div class="p-3 bg-green-100 rounded-full">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div>
                    <h2 class="font-semibold text-gray-800">Semua tagihan lunas! ✅</h2>
                    <p class="text-sm text-gray-500">Tidak ada tagihan yang perlu dibayar saat ini.</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Riwayat Tagihan — Hanya 3 Terbaru --}}
    <div class="bg-white rounded-xl shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold text-gray-700">Riwayat Tagihan</h2>
            @if ($riwayatTagihan->total() > 3)
                <button onclick="document.getElementById('modalRiwayat').classList.remove('hidden')"
                    class="text-sm text-blue-600 hover:underline font-medium">
                    Lihat semua ({{ $riwayatTagihan->total() }}) →
                </button>
            @endif
        </div>

        @if ($riwayatTagihan->count() > 0)
            <div class="space-y-3">
                {{-- Hanya tampilkan 3 terbaru --}}
                @foreach ($riwayatTagihan->take(3) as $tagihan)
                    <div class="border rounded-lg p-4 hover:bg-gray-50 transition">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-semibold text-gray-800">
                                    {{ \Carbon\Carbon::create()->month($tagihan->bulan_periode)->isoFormat('MMMM') }}
                                    {{ $tagihan->tahun_periode }}
                                </p>
                                @if ($tagihan->pencatatanMeter)
                                    <p class="text-sm text-gray-500 mt-0.5">
                                        Pemakaian: {{ number_format($tagihan->pencatatanMeter->volume_m3, 2) }} m³
                                    </p>
                                @endif
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-gray-800">
                                    Rp {{ number_format($tagihan->total_tagihan, 0, ',', '.') }}
                                </p>
                                @if ($tagihan->status_bayar === 'lunas')
                                    <span
                                        class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-medium">Lunas</span>
                                @elseif($tagihan->status_bayar === 'mencicil')
                                    <span
                                        class="px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">Mencicil</span>
                                @else
                                    <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-xs font-medium">Belum
                                        Lunas</span>
                                @endif
                            </div>
                        </div>

                        {{-- Progress bar --}}
                        @if ($tagihan->total_tagihan > 0)
                            <div class="mt-3">
                                <div class="flex justify-between text-xs text-gray-400 mb-1">
                                    <span>Progress Pembayaran</span>
                                    <span>{{ number_format(($tagihan->total_terbayar / $tagihan->total_tagihan) * 100, 0) }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="h-2 rounded-full {{ $tagihan->status_bayar === 'lunas' ? 'bg-green-500' : 'bg-blue-500' }}"
                                        style="width: {{ ($tagihan->total_terbayar / $tagihan->total_tagihan) * 100 }}%">
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Detail Biaya --}}
                        <div class="mt-3 grid grid-cols-3 gap-2 text-xs text-gray-500">
                            <div>
                                <span class="block text-gray-400">Biaya Beban</span>
                                <span>Rp {{ number_format($tagihan->biaya_beban, 0, ',', '.') }}</span>
                            </div>
                            <div>
                                <span class="block text-gray-400">Biaya Pemakaian</span>
                                <span>Rp {{ number_format($tagihan->biaya_pemakaian, 0, ',', '.') }}</span>
                            </div>
                            <div>
                                <span class="block text-gray-400">Sisa Tagihan</span>
                                <span
                                    class="{{ $tagihan->sisa_tagihan > 0 ? 'text-red-600 font-semibold' : 'text-green-600' }}">
                                    Rp {{ number_format($tagihan->sisa_tagihan, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        {{-- Bukti Pembayaran --}}
                        @if ($tagihan->pembayaran->count() > 0)
                            <div class="mt-3 border-t pt-3">
                                <p class="text-xs font-semibold text-gray-500 mb-2">Bukti Pembayaran:</p>
                                <div class="space-y-2">
                                    @foreach ($tagihan->pembayaran as $p)
                                        <div class="flex items-center justify-between bg-gray-50 rounded-lg p-2">
                                            <div class="flex items-center gap-3">
                                                @if ($p->foto_bukti)
                                                    <a href="{{ asset('storage/' . $p->foto_bukti) }}" target="_blank">
                                                        <img src="{{ asset('storage/' . $p->foto_bukti) }}" alt="Bukti"
                                                            class="w-12 h-12 object-cover rounded-lg border hover:opacity-80 transition cursor-pointer">
                                                    </a>
                                                @else
                                                    <div
                                                        class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
                                                        <svg class="w-5 h-5 text-gray-400" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                    </div>
                                                @endif
                                                <div>
                                                    <p class="text-xs font-medium text-gray-700">
                                                        Rp {{ number_format($p->nominal_bayar, 0, ',', '.') }}
                                                    </p>
                                                    <p class="text-xs text-gray-400">
                                                        {{ \Carbon\Carbon::parse($p->tanggal_bayar)->isoFormat('D MMM Y') }}
                                                    </p>
                                                    @if ($p->keterangan)
                                                        <p class="text-xs text-gray-400">{{ $p->keterangan }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                            <span class="text-xs text-gray-400">{{ $p->akunKas->nama_akun ?? '-' }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8 text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <p>Belum ada riwayat tagihan</p>
            </div>
        @endif
    </div>

    {{-- MODAL Semua Riwayat --}}
    <div id="modalRiwayat" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-screen overflow-hidden flex flex-col">

            {{-- Modal Header --}}
            <div class="flex justify-between items-center p-6 border-b">
                <h2 class="text-lg font-bold text-gray-800">Semua Riwayat Tagihan</h2>
                <button onclick="document.getElementById('modalRiwayat').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="overflow-y-auto p-6 space-y-3">
                @forelse($riwayatTagihan as $tagihan)
                    <div class="border rounded-lg p-4">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <p class="font-semibold text-gray-800">
                                    {{ \Carbon\Carbon::create()->month($tagihan->bulan_periode)->isoFormat('MMMM') }}
                                    {{ $tagihan->tahun_periode }}
                                </p>
                                @if ($tagihan->pencatatanMeter)
                                    <p class="text-sm text-gray-500">
                                        💧 {{ number_format($tagihan->pencatatanMeter->volume_m3, 2) }} m³
                                    </p>
                                @endif
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-gray-800">
                                    Rp {{ number_format($tagihan->total_tagihan, 0, ',', '.') }}
                                </p>
                                @if ($tagihan->status_bayar === 'lunas')
                                    <span
                                        class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-medium">Lunas</span>
                                @elseif($tagihan->status_bayar === 'mencicil')
                                    <span
                                        class="px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">Mencicil</span>
                                @else
                                    <span
                                        class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-xs font-medium">Belum
                                        Lunas</span>
                                @endif
                            </div>
                        </div>

                        {{-- Progress --}}
                        @if ($tagihan->total_tagihan > 0)
                            <div class="mb-2">
                                <div class="w-full bg-gray-200 rounded-full h-1.5">
                                    <div class="h-1.5 rounded-full {{ $tagihan->status_bayar === 'lunas' ? 'bg-green-500' : 'bg-blue-500' }}"
                                        style="width: {{ ($tagihan->total_terbayar / $tagihan->total_tagihan) * 100 }}%">
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Detail --}}
                        <div class="grid grid-cols-3 gap-2 text-xs text-gray-500 mb-2">
                            <div>
                                <span class="block text-gray-400">Biaya Beban</span>
                                Rp {{ number_format($tagihan->biaya_beban, 0, ',', '.') }}
                            </div>
                            <div>
                                <span class="block text-gray-400">Biaya Pemakaian</span>
                                Rp {{ number_format($tagihan->biaya_pemakaian, 0, ',', '.') }}
                            </div>
                            <div>
                                <span class="block text-gray-400">Sisa</span>
                                <span
                                    class="{{ $tagihan->sisa_tagihan > 0 ? 'text-red-600 font-semibold' : 'text-green-600' }}">
                                    Rp {{ number_format($tagihan->sisa_tagihan, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        {{-- Bukti Pembayaran --}}
                        @if ($tagihan->pembayaran->count() > 0)
                            <div class="border-t pt-2">
                                <p class="text-xs font-semibold text-gray-500 mb-2">Bukti Pembayaran:</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($tagihan->pembayaran as $p)
                                        <div class="flex items-center gap-2 bg-gray-50 rounded-lg p-2 text-xs">
                                            @if ($p->foto_bukti)
                                                <a href="{{ asset('storage/' . $p->foto_bukti) }}" target="_blank">
                                                    <img src="{{ asset('storage/' . $p->foto_bukti) }}" alt="Bukti"
                                                        class="w-10 h-10 object-cover rounded border hover:opacity-80 cursor-pointer">
                                                </a>
                                            @else
                                                <div
                                                    class="w-10 h-10 bg-gray-200 rounded flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-gray-400" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                </div>
                                            @endif
                                            <div>
                                                <p class="font-medium text-gray-700">Rp
                                                    {{ number_format($p->nominal_bayar, 0, ',', '.') }}</p>
                                                <p class="text-gray-400">
                                                    {{ \Carbon\Carbon::parse($p->tanggal_bayar)->isoFormat('D MMM Y') }}
                                                </p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-center text-gray-400 py-4">Belum ada riwayat tagihan</p>
                @endforelse
            </div>

            {{-- Modal Footer --}}
            <div class="p-4 border-t">
                <button onclick="document.getElementById('modalRiwayat').classList.add('hidden')"
                    class="w-full bg-gray-100 text-gray-700 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    {{-- Tutup modal kalau klik di luar --}}
    <script>
        document.getElementById('modalRiwayat').addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
            }
        });
    </script>

@endsection
