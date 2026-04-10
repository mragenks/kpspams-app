@extends('layouts.admin')
@section('title', 'Buku Kas')

@section('content')

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6 flex items-center">
            <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" />
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total Masuk</p>
                <p class="text-xl font-bold text-green-600">
                    Rp {{ number_format($totalMasuk, 0, ',', '.') }}
                </p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 flex items-center">
            <div class="p-3 rounded-full bg-red-100 text-red-600 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6" />
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total Keluar</p>
                <p class="text-xl font-bold text-red-600">
                    Rp {{ number_format($totalKeluar, 0, ',', '.') }}
                </p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 flex items-center">
            <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500">Selisih Bulan Ini</p>
                <p class="text-xl font-bold {{ $totalMasuk - $totalKeluar >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                    Rp {{ number_format($totalMasuk - $totalKeluar, 0, ',', '.') }}
                </p>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" action="{{ route('ketua.arus-kas.index') }}" class="flex gap-4 items-end flex-wrap">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Bulan</label>
                <select name="bulan"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach (range(1, 12) as $b)
                        <option value="{{ $b }}" {{ $bulan == $b ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($b)->isoFormat('MMMM') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Tahun</label>
                <select name="tahun"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-28 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach (range(date('Y'), 2020) as $t)
                        <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Akun Kas</label>
                <select name="akun_kas_id"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Akun</option>
                    @foreach ($akunKasList as $ak)
                        <option value="{{ $ak->id }}" {{ $akunKasId == $ak->id ? 'selected' : '' }}>
                            {{ $ak->nama_akun }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                Filter
            </button>
        </form>
    </div>

    {{-- Tabel --}}
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h2 class="text-lg font-semibold text-gray-700">
                Buku Kas — {{ \Carbon\Carbon::create()->month($bulan)->isoFormat('MMMM') }} {{ $tahun }}
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th class="px-6 py-4">No</th>
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4">Keterangan</th>
                        <th class="px-6 py-4">Akun Kas</th>
                        <th class="px-6 py-4">Jenis</th>
                        <th class="px-6 py-4">Nominal</th>
                        <th class="px-6 py-4">No. Bukti</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($arusKas as $index => $ak)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-gray-500">
                                {{ ($arusKas->currentPage() - 1) * $arusKas->perPage() + $index + 1 }}
                            </td>
                            <td class="px-6 py-4 text-gray-700">
                                {{ \Carbon\Carbon::parse($ak->tanggal)->isoFormat('D MMM Y') }}
                            </td>
                            <td class="px-6 py-4 text-gray-800">{{ $ak->keterangan }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $ak->akunKas->nama_akun }}</td>
                            <td class="px-6 py-4">
                                @if (in_array($ak->jenis_transaksi, ['masuk', 'mutasi_masuk']))
                                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">
                                        {{ $ak->jenis_transaksi === 'mutasi_masuk' ? 'Mutasi Masuk' : 'Masuk' }}
                                    </span>
                                @else
                                    <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium">
                                        {{ $ak->jenis_transaksi === 'mutasi_keluar' ? 'Mutasi Keluar' : 'Keluar' }}
                                    </span>
                                @endif
                            </td>
                            <td
                                class="px-6 py-4 font-semibold {{ in_array($ak->jenis_transaksi, ['masuk', 'mutasi_masuk']) ? 'text-green-600' : 'text-red-600' }}">
                                {{ in_array($ak->jenis_transaksi, ['masuk', 'mutasi_masuk']) ? '+' : '-' }}
                                Rp {{ number_format($ak->nominal, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-gray-500">{{ $ak->no_bukti ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-gray-400">
                                Belum ada transaksi di periode ini
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($arusKas->hasPages())
            <div class="p-4 border-t">
                {{ $arusKas->links() }}
            </div>
        @endif
    </div>
@endsection
