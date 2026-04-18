@extends('layouts.admin')

@section('title', 'Pusat Laporan PAMSIMAS')

@section('content')
    <div class="max-w-6xl mx-auto">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6">Pusat Laporan (Ketua)</h2>

        {{-- MENU NAVIGASI TABS --}}
        <div class="flex space-x-2 mb-6 border-b border-gray-200 pb-2 overflow-x-auto snap-x">
            <a href="{{ route('ketua.laporan.index', ['tab' => 'arus-kas']) }}"
                class="snap-start px-5 py-2.5 rounded-t-lg text-sm font-medium transition-colors whitespace-nowrap {{ $tab == 'arus-kas' ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                Laporan Arus Kas
            </a>
            <a href="{{ route('ketua.laporan.index', ['tab' => 'tagihan']) }}"
                class="snap-start px-5 py-2.5 rounded-t-lg text-sm font-medium transition-colors whitespace-nowrap {{ $tab == 'tagihan' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                Laporan Tagihan Air
            </a>
            <a href="{{ route('ketua.laporan.index', ['tab' => 'inventaris']) }}"
                class="snap-start px-5 py-2.5 rounded-t-lg text-sm font-medium transition-colors whitespace-nowrap {{ $tab == 'inventaris' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                Laporan Mutasi Inventaris
            </a>
        </div>

        {{-- TAB 1: ARUS KAS --}}
        @if ($tab == 'arus-kas')
            <div class="bg-white rounded-b-lg rounded-tr-lg shadow p-4 sm:p-6 border-t-4 border-gray-800">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                    <div>
                        <h3 class="text-lg font-medium text-gray-800">Buku Kas Umum</h3>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
                        <a href="{{ route('ketua.laporan.aruskas.pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
                            target="_blank"
                            class="w-full sm:w-auto justify-center bg-red-600 hover:bg-red-700 text-white text-sm font-medium py-2.5 px-4 rounded-lg flex items-center transition-colors">
                            Cetak PDF
                        </a>
                        <form action="{{ route('ketua.laporan.aruskas.excel') }}" method="POST" class="w-full sm:w-auto">
                            @csrf
                            <input type="hidden" name="start_date" value="{{ $startDate }}">
                            <input type="hidden" name="end_date" value="{{ $endDate }}">
                            <button type="submit"
                                class="w-full sm:w-auto justify-center bg-green-600 hover:bg-green-700 text-white text-sm font-medium py-2.5 px-4 rounded-lg flex items-center transition-colors">
                                Download Excel
                            </button>
                        </form>
                    </div>
                </div>

                <form action="{{ route('ketua.laporan.index') }}" method="GET"
                    class="bg-gray-50 p-4 rounded-lg border border-gray-200 mb-6 flex flex-col md:flex-row gap-4 md:items-end">
                    <input type="hidden" name="tab" value="arus-kas">
                    <div class="w-full md:flex-1">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Dari Tanggal</label>
                        <input type="date" name="start_date" value="{{ $startDate }}" onchange="this.form.submit()"
                            class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div class="w-full md:flex-1">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="{{ $endDate }}" onchange="this.form.submit()"
                            class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                </form>

                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="w-full text-sm text-left text-gray-600">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-700">
                            <tr>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="px-4 py-3">Keterangan</th>
                                <th class="px-4 py-3 text-right">Debit</th>
                                <th class="px-4 py-3 text-right">Kredit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data['arusKas'] as $kas)
                                <tr class="border-t hover:bg-gray-50">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($kas->tanggal)->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3">{{ $kas->keterangan }}</td>
                                    <td class="px-4 py-3 text-right text-green-600 whitespace-nowrap">
                                        {{ in_array($kas->jenis_transaksi, ['masuk', 'mutasi_masuk']) ? 'Rp ' . number_format($kas->nominal, 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-red-600 whitespace-nowrap">
                                        {{ in_array($kas->jenis_transaksi, ['keluar', 'mutasi_keluar']) ? 'Rp ' . number_format($kas->nominal, 0, ',', '.') : '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-gray-500">Tidak ada data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($data['arusKas']->hasPages())
                    <div class="mt-4">{{ $data['arusKas']->appends(request()->query())->links() }}</div>
                @endif
            </div>
        @endif

        {{-- TAB 2: TAGIHAN AIR --}}
        @if ($tab == 'tagihan')
            <div
                class="bg-white rounded-b-lg rounded-tl-lg rounded-tr-lg md:rounded-tl-none shadow p-4 sm:p-6 border-t-4 border-blue-600">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                    <div>
                        <h3 class="text-lg font-medium text-gray-800">Tagihan & Tunggakan</h3>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
                        <a href="{{ route('ketua.laporan.tagihan.pdf', ['start_date' => $startDate, 'end_date' => $endDate, 'search_pelanggan' => $data['searchPelanggan'] ?? '']) }}"
                            target="_blank"
                            class="w-full sm:w-auto justify-center bg-red-600 hover:bg-red-700 text-white text-sm font-medium py-2.5 px-4 rounded-lg flex items-center transition-colors">
                            Cetak PDF
                        </a>
                        <form action="{{ route('ketua.laporan.tagihan.excel') }}" method="POST" class="w-full sm:w-auto">
                            @csrf
                            <input type="hidden" name="start_date" value="{{ $startDate }}">
                            <input type="hidden" name="end_date" value="{{ $endDate }}">
                            <input type="hidden" name="search_pelanggan" value="{{ $data['searchPelanggan'] ?? '' }}">
                            <button type="submit"
                                class="w-full sm:w-auto justify-center bg-green-600 hover:bg-green-700 text-white text-sm font-medium py-2.5 px-4 rounded-lg flex items-center transition-colors">
                                Download Excel
                            </button>
                        </form>
                    </div>
                </div>

                <form action="{{ route('ketua.laporan.index') }}" method="GET"
                    class="bg-blue-50 p-4 rounded-lg border border-blue-100 mb-6 flex flex-col md:flex-row gap-4 md:items-end">
                    <input type="hidden" name="tab" value="tagihan">
                    <div class="w-full md:w-1/4">
                        <label class="block text-xs font-semibold text-blue-800 mb-1">Dari Tanggal</label>
                        <input type="date" name="start_date" value="{{ $startDate }}" onchange="this.form.submit()"
                            class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div class="w-full md:w-1/4">
                        <label class="block text-xs font-semibold text-blue-800 mb-1">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="{{ $endDate }}" onchange="this.form.submit()"
                            class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div class="w-full md:w-2/4">
                        <label class="block text-xs font-semibold text-blue-800 mb-1">Cari Pelanggan (Enter)</label>
                        <input type="text" name="search_pelanggan" value="{{ $data['searchPelanggan'] ?? '' }}"
                            placeholder="Nama/Kode Pelanggan..." class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                </form>

                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="w-full text-sm text-left text-gray-600">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-700">
                            <tr>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="px-4 py-3">Pelanggan</th>
                                <th class="px-4 py-3">Tagihan</th>
                                <th class="px-4 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data['tagihan'] as $tagih)
                                <tr class="border-t hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        {{ \Carbon\Carbon::parse($tagih->pencatatanMeter?->tanggal_pencatatan ?? $tagih->created_at)->format('d/m/Y') }}
                                    </td>
                                    <td class="px-4 py-3"><span
                                            class="font-medium text-gray-800">{{ $tagih->pelanggan->nama_pelanggan }}</span><br><span
                                            class="text-xs text-gray-500">{{ $tagih->pelanggan->kode_pelanggan }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-red-600 font-medium">Rp
                                        {{ number_format($tagih->total_tagihan, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3">{{ strtoupper(str_replace('_', ' ', $tagih->status_bayar)) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-gray-500">Tidak ada tagihan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($data['tagihan']->hasPages())
                    <div class="mt-4">{{ $data['tagihan']->appends(request()->query())->links() }}</div>
                @endif
            </div>
        @endif

        {{-- TAB 3: INVENTARIS --}}
        @if ($tab == 'inventaris')
            <div class="bg-white rounded-b-lg rounded-tl-lg shadow p-4 sm:p-6 border-t-4 border-purple-600">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                    <div>
                        <h3 class="text-lg font-medium text-gray-800">Mutasi Inventaris</h3>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
                        <a href="{{ route('ketua.laporan.inventaris.pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
                            target="_blank"
                            class="w-full sm:w-auto justify-center bg-red-600 hover:bg-red-700 text-white text-sm font-medium py-2.5 px-4 rounded-lg flex items-center">
                            Cetak PDF
                        </a>
                        <form action="{{ route('ketua.laporan.inventaris.excel') }}" method="POST"
                            class="w-full sm:w-auto">
                            @csrf
                            <input type="hidden" name="start_date" value="{{ $startDate }}">
                            <input type="hidden" name="end_date" value="{{ $endDate }}">
                            <button type="submit"
                                class="w-full sm:w-auto justify-center bg-green-600 hover:bg-green-700 text-white text-sm font-medium py-2.5 px-4 rounded-lg flex items-center">
                                Download Excel
                            </button>
                        </form>
                    </div>
                </div>

                <form action="{{ route('ketua.laporan.index') }}" method="GET"
                    class="bg-purple-50 p-4 rounded-lg border border-purple-100 mb-6 flex flex-col md:flex-row gap-4 md:items-end">
                    <input type="hidden" name="tab" value="inventaris">
                    <div class="w-full md:flex-1">
                        <label class="block text-xs font-semibold text-purple-800 mb-1">Dari Tanggal</label>
                        <input type="date" name="start_date" value="{{ $startDate }}"
                            onchange="this.form.submit()" class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div class="w-full md:flex-1">
                        <label class="block text-xs font-semibold text-purple-800 mb-1">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="{{ $endDate }}" onchange="this.form.submit()"
                            class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                </form>

                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="w-full text-sm text-left text-gray-600">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-700">
                            <tr>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="px-4 py-3">Barang</th>
                                <th class="px-4 py-3">Mutasi</th>
                                <th class="px-4 py-3 text-right">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data['inventaris'] as $inv)
                                <tr class="border-t hover:bg-gray-50">
                                    <td class="px-4 py-3">{{ \Carbon\Carbon::parse($inv->tanggal)->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3"><span
                                            class="font-medium text-gray-800">{{ $inv->inventaris->nama_barang }}</span>
                                    </td>
                                    <td class="px-4 py-3">{{ strtoupper($inv->jenis_mutasi) }}</td>
                                    <td class="px-4 py-3 font-semibold text-right">{{ $inv->jumlah }} unit</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-gray-500">Tidak ada mutasi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
