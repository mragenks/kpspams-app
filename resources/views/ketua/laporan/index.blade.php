@extends('layouts.admin')
@section('title', 'Laporan PAMSIMAS')

@section('content')
    <div class="max-w-4xl mx-auto">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6">Pusat Laporan</h2>

        {{-- Laporan Arus Kas --}}
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="mb-4 border-b pb-4">
                <h3 class="text-lg font-medium text-gray-800">Laporan Buku Kas Umum (Excel)</h3>
                <p class="text-sm text-gray-500">Unduh rincian arus kas masuk dan keluar berdasarkan rentang tanggal.</p>
            </div>
            <form action="{{ route('ketua.laporan.aruskas.excel') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                        <input type="date" name="start_date" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
                        <input type="date" name="end_date" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg flex items-center transition">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Download Excel
                    </button>
                </div>
            </form>
        </div>

        {{-- Laporan Tagihan --}}
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="mb-4 border-b pb-4">
                <h3 class="text-lg font-medium text-gray-800">Laporan Tagihan & Tunggakan (Excel)</h3>
                <p class="text-sm text-gray-500">Unduh rincian tagihan per periode bulan tertentu.</p>
            </div>
            <form action="{{ route('ketua.laporan.tagihan.excel') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bulan Periode</label>
                        <select name="bulan" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih Bulan --</option>
                            @foreach (range(1, 12) as $b)
                                <option value="{{ $b }}">
                                    {{ \Carbon\Carbon::create()->month($b)->isoFormat('MMMM') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tahun Periode</label>
                        <input type="number" name="tahun" value="{{ date('Y') }}" required min="2000"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg flex items-center transition">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Download Laporan Tagihan
                    </button>
                </div>
            </form>
        </div>

        {{-- Laporan Inventaris --}}
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="mb-4 border-b pb-4">
                <h3 class="text-lg font-medium text-gray-800">Laporan Mutasi Inventaris (Excel)</h3>
                <p class="text-sm text-gray-500">Unduh rekam jejak barang masuk, keluar, dan penyesuaian aset.</p>
            </div>
            <form action="{{ route('ketua.laporan.inventaris.excel') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                        <input type="date" name="start_date" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
                        <input type="date" name="end_date" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit"
                        class="bg-purple-600 hover:bg-purple-700 text-white font-medium py-2 px-4 rounded-lg flex items-center transition">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                        Download Excel Mutasi
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
