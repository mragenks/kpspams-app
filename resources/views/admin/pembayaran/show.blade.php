@extends('layouts.admin')
@section('title', 'Detail Pembayaran')

@section('content')
    <div class="max-w-3xl mx-auto space-y-6">

        {{-- Info Tagihan --}}
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <div class="flex justify-between items-center mb-6 border-b pb-4 sm:border-none sm:pb-0">
                <h2 class="text-base sm:text-lg font-semibold text-gray-700">Detail Tagihan</h2>
                <a href="{{ route('admin.pembayaran.index') }}"
                    class="text-sm text-blue-600 hover:text-blue-800 transition flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span class="hidden sm:inline">Kembali</span>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                <div>
                    <p class="text-[10px] sm:text-xs text-gray-400 uppercase tracking-wide">Pelanggan</p>
                    <p class="font-semibold text-gray-800 text-sm sm:text-base">{{ $pembayaran->pelanggan->nama_pelanggan }}
                    </p>
                    <p class="text-xs text-blue-600 font-mono mt-0.5">{{ $pembayaran->pelanggan->kode_pelanggan }}</p>
                </div>
                <div>
                    <p class="text-[10px] sm:text-xs text-gray-400 uppercase tracking-wide">Periode</p>
                    <p class="font-semibold text-gray-800 text-sm sm:text-base">
                        {{ \Carbon\Carbon::create()->month($pembayaran->bulan_periode)->isoFormat('MMMM') }}
                        {{ $pembayaran->tahun_periode }}
                    </p>
                </div>
            </div>

            <div
                class="grid grid-cols-2 gap-4 bg-gray-50 border border-gray-100 rounded-lg p-4 mb-6 divide-x divide-gray-200">
                <div class="px-2">
                    <p class="text-[10px] sm:text-xs text-gray-500 uppercase tracking-wide mb-1">Volume</p>
                    <p class="font-bold text-blue-600 text-lg sm:text-xl">
                        {{ number_format($pembayaran->pencatatanMeter->volume_m3, 2) }} <span
                            class="text-sm font-normal">m³</span>
                    </p>
                </div>
                <div class="px-2 pl-4">
                    <p class="text-[10px] sm:text-xs text-gray-500 uppercase tracking-wide mb-1">Status</p>
                    @if ($pembayaran->status_bayar === 'lunas')
                        <span
                            class="inline-block px-3 py-1 bg-green-100 text-green-700 rounded-full text-[10px] sm:text-xs font-bold uppercase tracking-wider">Lunas</span>
                    @elseif($pembayaran->status_bayar === 'mencicil')
                        <span
                            class="inline-block px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-[10px] sm:text-xs font-bold uppercase tracking-wider">Mencicil</span>
                    @else
                        <span
                            class="inline-block px-3 py-1 bg-red-100 text-red-700 rounded-full text-[10px] sm:text-xs font-bold uppercase tracking-wider">Belum
                            Lunas</span>
                    @endif
                </div>
            </div>

            <div class="mt-4 space-y-2.5 text-xs sm:text-sm">
                <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                    <span class="text-gray-500">Biaya Beban</span>
                    <span class="font-medium text-gray-800">Rp
                        {{ number_format($pembayaran->biaya_beban, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                    <span class="text-gray-500">Biaya Pemakaian</span>
                    <span class="font-medium text-gray-800">Rp
                        {{ number_format($pembayaran->biaya_pemakaian, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center pt-2 font-bold text-gray-800">
                    <span>Total Tagihan</span>
                    <span>Rp {{ number_format($pembayaran->total_tagihan, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center py-2 text-green-600 font-medium">
                    <span>Total Terbayar</span>
                    <span>Rp {{ number_format($pembayaran->total_terbayar, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center py-2 font-bold text-red-600 bg-red-50 rounded px-2 -mx-2">
                    <span>Sisa Tagihan</span>
                    <span>Rp {{ number_format($pembayaran->sisa_tagihan, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        {{-- Form Bayar --}}
        @if ($pembayaran->status_bayar !== 'lunas')
            <div class="bg-white rounded-lg shadow p-4 sm:p-6 border-t-4 border-blue-500">
                <h3 class="text-base font-semibold text-gray-800 mb-4">Catat Pembayaran</h3>

                <form action="{{ route('admin.pembayaran.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="tagihan_id" value="{{ $pembayaran->id }}">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Nominal Bayar <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="nominal_bayar"
                                value="{{ old('nominal_bayar', $pembayaran->sisa_tagihan) }}" min="1"
                                max="{{ $pembayaran->sisa_tagihan }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                            <p class="text-[10px] text-gray-400 mt-1">
                                Maksimal: Rp {{ number_format($pembayaran->sisa_tagihan, 0, ',', '.') }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Akun Kas <span class="text-red-500">*</span>
                            </label>
                            <select name="akun_kas_id"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                <option value="">-- Pilih Akun Kas --</option>
                                @foreach ($akunKas as $ak)
                                    <option value="{{ $ak->id }}">
                                        {{ $ak->nama_akun }} (Saldo: Rp {{ number_format($ak->saldo, 0, ',', '.') }})
                                    </option>
                                @endforeach
                            </select>
                            @if ($akunKas->isEmpty())
                                <p class="text-xs text-red-500 mt-1">
                                    Belum ada akun kas!
                                    <a href="{{ route('admin.akun-kas.create') }}" class="underline font-medium">Buat akun
                                        kas dulu</a>
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Tanggal Bayar <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="tanggal_bayar" value="{{ old('tanggal_bayar', date('Y-m-d')) }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                            <input type="text" name="keterangan" value="{{ old('keterangan') }}"
                                placeholder="Opsional..."
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    {{-- Foto Bukti --}}
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Foto Bukti Pembayaran <span class="text-gray-400 font-normal">(Opsional)</span>
                        </label>
                        <input type="file" name="foto_bukti" accept="image/*"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 file:mr-4 file:py-1 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition bg-white"
                            onchange="previewFoto(this, 'preview_bukti')">
                        <p class="text-[10px] text-gray-400 mt-1">Format: JPG, JPEG, PNG. Maksimal 2MB</p>
                        <div id="preview_bukti" class="hidden mt-3">
                            <img src="" alt="Preview" class="w-32 h-32 object-cover rounded-lg border shadow-sm">
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full sm:w-auto flex justify-center items-center gap-2 bg-green-600 text-white px-6 py-2.5 rounded-lg text-sm font-bold hover:bg-green-700 transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Proses Pembayaran
                    </button>
                </form>
            </div>
        @endif

        {{-- Riwayat Pembayaran --}}
        @if ($pembayaran->pembayaran->count() > 0)
            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-4 border-b pb-2">Riwayat Pembayaran</h3>

                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="w-full text-sm text-left">
                        <thead
                            class="bg-gray-50 text-gray-600 uppercase text-[10px] sm:text-xs tracking-wider border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 w-8">No</th>
                                <th class="px-4 py-3 whitespace-nowrap">Tanggal</th>
                                <th class="px-4 py-3">Nominal</th>
                                <th class="hidden sm:table-cell px-4 py-3">Akun Kas</th>
                                <th class="hidden md:table-cell px-4 py-3">Keterangan</th>
                                <th class="px-4 py-3 text-center">Bukti</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($pembayaran->pembayaran as $index => $p)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 text-gray-800 font-medium whitespace-nowrap text-xs sm:text-sm">
                                        {{ \Carbon\Carbon::parse($p->tanggal_bayar)->isoFormat('D MMM Y') }}
                                    </td>
                                    <td class="px-4 py-3 font-bold text-green-600 whitespace-nowrap">
                                        Rp {{ number_format($p->nominal_bayar, 0, ',', '.') }}
                                    </td>
                                    <td class="hidden sm:table-cell px-4 py-3 text-gray-600 text-xs">
                                        {{ $p->akunKas->nama_akun ?? '-' }}
                                    </td>
                                    <td
                                        class="hidden md:table-cell px-4 py-3 text-gray-500 text-xs truncate max-w-[150px]">
                                        {{ $p->keterangan ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if ($p->foto_bukti)
                                            <a href="{{ Storage::url($p->foto_bukti) }}" target="_blank"
                                                class="inline-block">
                                                <img src="{{ Storage::url($p->foto_bukti) }}" alt="Bukti"
                                                    class="w-10 h-10 object-cover rounded border border-gray-300 hover:opacity-80 transition cursor-pointer shadow-sm">
                                            </a>
                                        @else
                                            <span class="text-gray-300 text-xs">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 border-t border-gray-200">
                            <tr>
                                <td colspan="2"
                                    class="px-4 py-3 text-xs sm:text-sm font-bold text-gray-700 text-right uppercase tracking-wider">
                                    Total Terbayar:</td>
                                <td class="px-4 py-3 font-black text-green-600 text-sm sm:text-base whitespace-nowrap">
                                    Rp {{ number_format($pembayaran->pembayaran->sum('nominal_bayar'), 0, ',', '.') }}
                                </td>
                                <td class="hidden sm:table-cell"></td>
                                <td class="hidden md:table-cell"></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endif

    </div>

    <script>
        function previewFoto(input, previewId) {
            const preview = document.getElementById(previewId);
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.querySelector('img').src = e.target.result;
                    preview.classList.remove('hidden');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endsection
