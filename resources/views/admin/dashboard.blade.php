@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

        <div class="bg-white rounded-xl shadow p-4 flex items-center hover:shadow-md transition">
            <div class="p-2 lg:p-3 rounded-full bg-blue-100 text-blue-600 mr-3 flex-shrink-0">
                <svg class="w-5 h-5 lg:w-6 lg:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-gray-500 truncate">Pelanggan Aktif</p>
                <p class="text-xl lg:text-2xl font-bold text-gray-700">{{ $totalPelanggan }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow p-4 flex items-center hover:shadow-md transition">
            <div class="p-2 lg:p-3 rounded-full bg-red-100 text-red-600 mr-3 flex-shrink-0">
                <svg class="w-5 h-5 lg:w-6 lg:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-gray-500 truncate">Belum Lunas</p>
                <p class="text-xl lg:text-2xl font-bold text-red-600">{{ $tagihanBelumLunas }}</p>
                <p class="text-xs text-gray-400 truncate hidden lg:block">
                    Piutang: Rp {{ number_format($totalPiutang, 0, ',', '.') }}
                </p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow p-4 flex items-center hover:shadow-md transition">
            <div class="p-2 lg:p-3 rounded-full bg-green-100 text-green-600 mr-3 flex-shrink-0">
                <svg class="w-5 h-5 lg:w-6 lg:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-gray-500 truncate">Total Saldo</p>
                <p class="text-base lg:text-2xl font-bold text-green-600 truncate">
                    Rp {{ number_format($totalSaldo, 0, ',', '.') }}
                </p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow p-4 flex items-center hover:shadow-md transition">
            <div class="p-2 lg:p-3 rounded-full bg-yellow-100 text-yellow-600 mr-3 flex-shrink-0">
                <svg class="w-5 h-5 lg:w-6 lg:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-gray-500 truncate">Total Aset</p>
                <p class="text-xl lg:text-2xl font-bold text-gray-700">{{ $totalAset }}</p>
                @if ($asetRusak > 0)
                    <p class="text-xs text-red-500 truncate">⚠️ {{ $asetRusak }} perlu perhatian</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Piutang info mobile --}}
    <div class="lg:hidden bg-red-50 border border-red-200 rounded-xl p-3 mb-4">
        <p class="text-sm text-red-700">
            💰 Total Piutang: <strong>Rp {{ number_format($totalPiutang, 0, ',', '.') }}</strong>
        </p>
    </div>

    {{-- Bulan Ini --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow p-4 lg:p-6">
            <p class="text-xs lg:text-sm text-gray-500 mb-1">Tagihan Bulan Ini</p>
            <p class="text-lg lg:text-xl font-bold text-gray-700">
                Rp {{ number_format($totalTagihanBulanIni, 0, ',', '.') }}
            </p>
            <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                @php
                    $pct = $totalTagihanBulanIni > 0 ? ($totalTerbayarBulanIni / $totalTagihanBulanIni) * 100 : 0;
                @endphp
                <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $pct }}%"></div>
            </div>
            <p class="text-xs text-gray-400 mt-1">
                Terkumpul: {{ number_format($pct, 0) }}%
            </p>
        </div>

        <div class="bg-white rounded-xl shadow p-4 lg:p-6">
            <p class="text-xs lg:text-sm text-gray-500 mb-1">Pemasukan Bulan Ini</p>
            <p class="text-lg lg:text-xl font-bold text-green-600">
                + Rp {{ number_format($pemasukanBulanIni, 0, ',', '.') }}
            </p>
            <p class="text-xs text-gray-400 mt-2">
                {{ \Carbon\Carbon::create()->month($bulan)->isoFormat('MMMM') }} {{ $tahun }}
            </p>
        </div>

        <div class="bg-white rounded-xl shadow p-4 lg:p-6">
            <p class="text-xs lg:text-sm text-gray-500 mb-1">Pengeluaran Bulan Ini</p>
            <p class="text-lg lg:text-xl font-bold text-red-600">
                - Rp {{ number_format($pengeluaranBulanIni, 0, ',', '.') }}
            </p>
            <p class="text-xs text-gray-400 mt-2">
                Selisih: Rp {{ number_format($pemasukanBulanIni - $pengeluaranBulanIni, 0, ',', '.') }}
            </p>
        </div>
    </div>

    {{-- Chart & Tagihan --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">

        {{-- Chart --}}
        <div class="bg-white rounded-xl shadow p-4 lg:p-6">
            <h3 class="text-sm lg:text-base font-semibold text-gray-700 mb-4">
                Arus Kas 6 Bulan Terakhir
            </h3>
            <canvas id="arusKasChart"></canvas>
        </div>

        {{-- Tagihan Belum Lunas --}}
        <div class="bg-white rounded-xl shadow p-4 lg:p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-sm lg:text-base font-semibold text-gray-700">Tagihan Belum Lunas</h3>
                <a href="{{ route('admin.pembayaran.index') }}"
                    class="text-xs lg:text-sm text-blue-600 hover:underline">Lihat semua →</a>
            </div>

            @forelse($tagihanTerbaru as $t)
                <div class="flex justify-between items-center py-3 border-b last:border-0">
                    <div class="min-w-0 mr-2">
                        <p class="text-sm font-medium text-gray-800 truncate">
                            {{ $t->pelanggan->nama_pelanggan }}
                        </p>
                        <p class="text-xs text-gray-400">
                            {{ \Carbon\Carbon::create()->month($t->bulan_periode)->isoFormat('MMMM') }}
                            {{ $t->tahun_periode }}
                        </p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-sm font-bold text-red-600">
                            Rp {{ number_format($t->sisa_tagihan, 0, ',', '.') }}
                        </p>
                        @if ($t->status_bayar === 'mencicil')
                            <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full">Mencicil</span>
                        @else
                            <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full">Belum Lunas</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-6 text-gray-400">
                    <p>✅ Semua tagihan bulan ini sudah lunas!</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Transaksi Terbaru --}}
    <div class="bg-white rounded-xl shadow p-4 lg:p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-sm lg:text-base font-semibold text-gray-700">Transaksi Kas Terbaru</h3>
            <a href="{{ route('admin.arus-kas.index') }}" class="text-xs lg:text-sm text-blue-600 hover:underline">Lihat
                semua →</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs lg:text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                    <tr>
                        <th class="px-3 lg:px-4 py-3 text-left">Tanggal</th>
                        <th class="px-3 lg:px-4 py-3 text-left">Keterangan</th>
                        <th class="px-3 lg:px-4 py-3 text-left hidden sm:table-cell">Akun</th>
                        <th class="px-3 lg:px-4 py-3 text-right">Nominal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($transaksiTerbaru as $t)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 lg:px-4 py-3 text-gray-500 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($t->tanggal)->isoFormat('D MMM Y') }}
                            </td>
                            <td class="px-3 lg:px-4 py-3 text-gray-700 max-w-xs">
                                <span class="block truncate">{{ Str::limit($t->keterangan, 30) }}</span>
                            </td>
                            <td class="px-3 lg:px-4 py-3 text-gray-500 hidden sm:table-cell">
                                {{ $t->akunKas->nama_akun }}
                            </td>
                            <td
                                class="px-3 lg:px-4 py-3 text-right font-semibold whitespace-nowrap
                        {{ in_array($t->jenis_transaksi, ['masuk', 'mutasi_masuk']) ? 'text-green-600' : 'text-red-600' }}">
                                {{ in_array($t->jenis_transaksi, ['masuk', 'mutasi_masuk']) ? '+' : '-' }}
                                Rp {{ number_format($t->nominal, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-gray-400">
                                Belum ada transaksi
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const chartData = @json($chartData);
        const ctx = document.getElementById('arusKasChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartData.map(d => d.bulan),
                datasets: [{
                        label: 'Pemasukan',
                        data: chartData.map(d => d.masuk),
                        backgroundColor: 'rgba(34, 197, 94, 0.7)',
                        borderColor: 'rgba(34, 197, 94, 1)',
                        borderWidth: 1,
                        borderRadius: 4,
                    },
                    {
                        label: 'Pengeluaran',
                        data: chartData.map(d => d.keluar),
                        backgroundColor: 'rgba(239, 68, 68, 0.7)',
                        borderColor: 'rgba(239, 68, 68, 1)',
                        borderWidth: 1,
                        borderRadius: 4,
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': Rp ' +
                                    context.raw.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                if (window.innerWidth < 768) {
                                    return 'Rp ' + (value / 1000) + 'K';
                                }
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });
    </script>

@endsection
