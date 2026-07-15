@extends('layouts.admin')
@section('title', 'Bayar Tunggakan Sekaligus')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">

        {{-- Detail Header --}}
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <div class="flex justify-between items-center mb-6 border-b pb-4 sm:border-none sm:pb-0">
                <h2 class="text-base sm:text-lg font-semibold text-gray-700">Bayar Sekaligus / Tunggakan Pelanggan</h2>
                <a href="{{ route('admin.pembayaran.index') }}"
                    class="text-sm text-blue-600 hover:text-blue-800 transition flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span>Kembali</span>
                </a>
            </div>

            {{-- Form Pilih Pelanggan --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Pilih Pelanggan <span class="text-red-500">*</span>
                </label>
                <select id="pelanggan_select" onchange="loadPelangganTunggakan(this.value)"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="">-- Pilih Pelanggan yang Memiliki Tunggakan --</option>
                    @foreach ($pelangganList as $p)
                        <option value="{{ $p->id }}" {{ isset($selectedPelanggan) && $selectedPelanggan->id == $p->id ? 'selected' : '' }}>
                            {{ $p->user->nama_lengkap }} ({{ $p->kode_pelanggan }})
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-400 mt-1">Hanya pelanggan dengan status aktif dan memiliki tunggakan/sisa tagihan yang muncul di sini.</p>
            </div>
        </div>

        @if (session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-3 rounded-lg text-sm">
                <p class="font-bold">Gagal!</p>
                <p>{{ session('error') }}</p>
            </div>
        @endif

        @if ($selectedPelanggan)
            {{-- Rincian Tunggakan --}}
            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-4 border-b pb-2">Rincian Tagihan Belum Lunas</h3>
                <div class="overflow-x-auto rounded-lg border border-gray-200 mb-6">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 text-gray-600 uppercase text-xs tracking-wider border-b">
                            <tr>
                                <th class="px-4 py-3 text-center">No</th>
                                <th class="px-4 py-3">Periode</th>
                                <th class="px-4 py-3 text-right">Total Tagihan</th>
                                <th class="px-4 py-3 text-right">Telah Dibayar</th>
                                <th class="px-4 py-3 text-right">Sisa Tunggakan</th>
                                <th class="px-4 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($outstandingBills as $index => $bill)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-3 text-center text-gray-500">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 font-semibold text-gray-800">
                                        {{ \Carbon\Carbon::create(null, $bill->bulan_periode, 1)->isoFormat('MMMM') }} {{ $bill->tahun_periode }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono text-gray-700">
                                        Rp {{ number_format($bill->total_tagihan, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono text-green-600">
                                        Rp {{ number_format($bill->total_terbayar, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono text-red-600 font-bold">
                                        Rp {{ number_format($bill->sisa_tagihan, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if ($bill->status_bayar === 'mencicil')
                                            <span class="inline-block px-2.5 py-0.5 bg-yellow-100 text-yellow-700 rounded-full text-[10px] font-bold uppercase">Mencicil</span>
                                        @else
                                            <span class="inline-block px-2.5 py-0.5 bg-red-100 text-red-700 rounded-full text-[10px] font-bold uppercase">Belum Lunas</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Total Box --}}
                <div class="bg-red-50 border border-red-100 rounded-xl p-4 mb-6 flex justify-between items-center">
                    <div>
                        <p class="text-xs text-red-700 uppercase tracking-wide font-semibold">Total Tunggakan Pelanggan</p>
                        <p class="text-[10px] text-red-500">Mencakup {{ $outstandingBills->count() }} periode tagihan air</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xl sm:text-2xl font-black text-red-600 font-mono">
                            Rp {{ number_format($totalTunggakan, 0, ',', '.') }}
                        </p>
                    </div>
                </div>

                {{-- Form Bayar --}}
                <form action="{{ route('admin.pembayaran.bayar_sekaligus_store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <input type="hidden" name="pelanggan_id" value="{{ $selectedPelanggan->id }}">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Nominal Pembayaran <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="nominal_bayar" id="nominal_bayar"
                                value="{{ old('nominal_bayar', $totalTunggakan) }}" min="1" max="{{ $totalTunggakan }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white font-mono"
                                oninput="validateInputNominal(this, {{ $totalTunggakan }})">
                            <p class="text-xs text-gray-400 mt-1">Masukkan nominal bayar (pembayaran akan otomatis didistribusikan dari periode tertua).</p>
                            @error('nominal_bayar')
                                <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Akun Kas Penerima <span class="text-red-500">*</span>
                            </label>
                            <select name="akun_kas_id" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                <option value="">-- Pilih Akun Kas --</option>
                                @foreach ($akunKas as $ak)
                                    <option value="{{ $ak->id }}" {{ old('akun_kas_id') == $ak->id ? 'selected' : '' }}>
                                        {{ $ak->nama_akun }} (Saldo: Rp {{ number_format($ak->saldo, 0, ',', '.') }})
                                    </option>
                                @endforeach
                            </select>
                            @error('akun_kas_id')
                                <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Tanggal Bayar <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="tanggal_bayar" value="{{ old('tanggal_bayar', date('Y-m-d')) }}" readonly
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-500 cursor-not-allowed pointer-events-none">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                            <input type="text" name="keterangan" value="{{ old('keterangan') }}"
                                placeholder="Contoh: Pembayaran kolektif..."
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('keterangan')
                                <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Foto Bukti Pembayaran <span class="text-gray-400 text-xs">(Opsional, Maks 5MB)</span>
                        </label>
                        <input type="file" name="foto_bukti" accept="image/*"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white file:mr-4 file:py-1 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition"
                            onchange="previewFoto(this, 'preview_bukti')">
                        <p class="text-xs text-gray-400 mt-1">Format: JPG, JPEG, PNG. Maksimal 5MB</p>
                        @error('foto_bukti')
                            <p class="text-red-500 text-xs mt-1 font-semibold">⚠️ {{ $message }}</p>
                        @enderror
                        <div id="preview_bukti" class="hidden mt-3">
                            <img src="" alt="Preview" class="w-40 h-40 object-cover rounded-lg border shadow-sm mx-auto">
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t">
                        <button type="submit"
                            class="w-full sm:w-auto text-center bg-green-600 text-white px-6 py-2.5 rounded-lg text-sm font-bold hover:bg-green-700 transition">
                            Proses Pembayaran Sekaligus
                        </button>
                        <a href="{{ route('admin.pembayaran.index') }}"
                            class="w-full sm:w-auto text-center bg-gray-500 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-600 transition">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        @endif

    </div>

    <script>
        function loadPelangganTunggakan(pelangganId) {
            if (pelangganId) {
                window.location.href = "{{ route('admin.pembayaran.bayar_sekaligus_form') }}?pelanggan_id=" + pelangganId;
            } else {
                window.location.href = "{{ route('admin.pembayaran.bayar_sekaligus_form') }}";
            }
        }

        function validateInputNominal(input, maxVal) {
            if (parseFloat(input.value) > maxVal) {
                input.value = maxVal;
            }
        }

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
