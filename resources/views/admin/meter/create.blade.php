@extends('layouts.admin')
@section('title', 'Catat Meter Air')

@section('content')
    {{-- Tambahkan CSS Tom Select --}}
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
    <style>
        /* Kustomisasi Tom Select agar persis seperti input Tailwind */
        .ts-control {
            border: 1px solid #d1d5db;
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            box-shadow: none;
            font-size: 0.875rem;
            background-color: #ffffff;
        }

        .ts-control.focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
        }

        .ts-dropdown {
            border-radius: 0.5rem;
            border-color: #d1d5db;
            font-size: 0.875rem;
            overflow: hidden;
        }

        .ts-dropdown .option:hover,
        .ts-dropdown .option.active {
            background-color: #eff6ff;
            color: #1d4ed8;
        }
    </style>

    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow p-4 sm:p-6">
        <h2 class="text-lg font-semibold text-gray-700 mb-6">Catat Meter Air</h2>

        <form action="{{ route('admin.meter.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Pelanggan <span
                        class="text-red-500">*</span></label>
                <select name="pelanggan_id" id="pelanggan_id" autocomplete="off"
                    placeholder="Ketik Kode atau Nama Pelanggan...">
                    <option value="" data-bulan="0" data-tahun="0">-- Pilih Pelanggan --</option>
                    @foreach ($pelanggan as $p)
                        @if (strtolower($p->status) === 'aktif')
                            <option value="{{ $p->id }}" data-meteran="{{ $p->meter_terakhir }}"
                                data-bulan="{{ $p->bulan_terakhir }}" data-tahun="{{ $p->tahun_terakhir }}"
                                {{ old('pelanggan_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->kode_pelanggan }} - {{ $p->nama_pelanggan }}
                            </option>
                        @endif
                    @endforeach
                </select>
                @error('pelanggan_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Kolom Periode & Tanggal Bersebelahan --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Periode Tagihan <span
                            class="text-red-500">*</span></label>
                    @php
                        $oldBulan = old('bulan_periode', date('n'));
                        $oldTahun = old('tahun_periode', date('Y'));
                        $oldPeriode = $oldTahun . '-' . str_pad($oldBulan, 2, '0', STR_PAD_LEFT);
                    @endphp

                    <input type="month" id="periode_input" value="{{ $oldPeriode }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                        onchange="updateHiddenPeriode()">

                    <input type="hidden" name="bulan_periode" id="bulan_periode" value="{{ $oldBulan }}">
                    <input type="hidden" name="tahun_periode" id="tahun_periode" value="{{ $oldTahun }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pencatatan <span
                            class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_pencatatan" value="{{ old('tanggal_pencatatan', date('Y-m-d')) }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Meter Awal (m³)</label>
                    <input type="number" id="meter_awal_display" disabled
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-500 font-mono"
                        placeholder="Otomatis dari bulan lalu">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Meter Akhir (m³) <span
                            class="text-red-500">*</span></label>
                    <input type="number" name="meter_akhir" id="meter_akhir" value="{{ old('meter_akhir') }}"
                        min="0" step="0.01"
                        class="w-full border rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 transition {{ $errors->has('meter_akhir') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}"
                        onchange="hitungPreview()" onkeyup="hitungPreview()">
                    @error('meter_akhir')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Foto Meteran --}}
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Foto Meteran <span class="text-gray-400 font-normal">(Opsional)</span>
                </label>
                <input type="file" name="foto_meteran" accept="image/*"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white file:mr-4 file:py-1 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition"
                    onchange="previewFoto(this, 'preview_meter')">
                <p class="text-xs text-gray-400 mt-1">Format: JPG, JPEG, PNG. Maksimal 2MB</p>
                <div id="preview_meter" class="hidden mt-3">
                    <img src="" alt="Preview" class="w-40 h-40 object-cover rounded-lg border shadow-sm">
                </div>
            </div>

            {{-- Preview Tagihan --}}
            <div id="preview_tagihan" class="hidden bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h3 class="text-sm font-semibold text-blue-800 mb-3 border-b border-blue-200 pb-2">Pratinjau Tagihan:</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>Volume Pemakaian:</span>
                        <span id="prev_volume" class="font-medium text-gray-800 font-mono">0 m³</span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Biaya Beban:</span>
                        <span id="prev_beban" class="font-medium text-gray-800">Rp 5.000</span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Biaya Pemakaian:</span>
                        <span id="prev_biaya" class="font-medium text-gray-800">Rp 0</span>
                    </div>
                    <div class="flex justify-between font-bold text-blue-700 pt-2 border-t border-blue-200 mt-1">
                        <span>Total Tagihan:</span>
                        <span id="prev_total" class="text-base">Rp 0</span>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <button type="submit"
                    class="w-full sm:w-auto text-center bg-blue-600 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                    Simpan Data
                </button>
                <a href="{{ route('admin.meter.index') }}"
                    class="w-full sm:w-auto text-center bg-gray-500 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-600 transition">
                    Batal
                </a>
            </div>
        </form>
    </div>

    {{-- Tambahkan JS Tom Select --}}
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new TomSelect("#pelanggan_id", {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                },
                onChange: function(value) {
                    loadMeterAwal(value);
                }
            });

            updateHiddenPeriode();

            const existingPelanggan = document.getElementById('pelanggan_id').value;
            if (existingPelanggan !== "") {
                loadMeterAwal(existingPelanggan);
            }
        });

        function updateHiddenPeriode() {
            const val = document.getElementById('periode_input').value;
            if (val) {
                const parts = val.split('-');
                document.getElementById('tahun_periode').value = parts[0];
                document.getElementById('bulan_periode').value = parseInt(parts[1], 10);
            }
        }

        function loadMeterAwal(pelangganId) {
            const select = document.getElementById('pelanggan_id');
            const selectedOpt = Array.from(select.options).find(opt => opt.value === pelangganId);

            if (!selectedOpt || !selectedOpt.value) {
                document.getElementById('periode_input').removeAttribute('min');
                document.getElementById('meter_awal_display').value = '';
                return;
            }

            const meteranAwal = selectedOpt.getAttribute('data-meteran');
            document.getElementById('meter_awal_display').value = meteranAwal ?? 0;

            const bulanTerakhir = parseInt(selectedOpt.getAttribute('data-bulan')) || 0;
            const tahunTerakhir = parseInt(selectedOpt.getAttribute('data-tahun')) || 0;
            const periodeInput = document.getElementById('periode_input');

            if (bulanTerakhir > 0 && tahunTerakhir > 0) {
                let nextBulan = bulanTerakhir + 1;
                let nextTahun = tahunTerakhir;
                if (nextBulan > 12) {
                    nextBulan = 1;
                    nextTahun++;
                }

                const minBulanStr = nextBulan.toString().padStart(2, '0');
                const minPeriode = `${nextTahun}-${minBulanStr}`;

                periodeInput.min = minPeriode;

                if (periodeInput.value < minPeriode) {
                    periodeInput.value = minPeriode;
                    updateHiddenPeriode();
                }
            } else {
                periodeInput.removeAttribute('min');
            }

            hitungPreview();
        }

        function hitungPreview() {
            let strAwal = document.getElementById('meter_awal_display').value.toString().replace(',', '.');
            let strAkhir = document.getElementById('meter_akhir').value.toString().replace(',', '.');

            const meterAwal = parseFloat(strAwal) || 0;
            const meterAkhir = parseFloat(strAkhir) || 0;
            const volume = meterAkhir - meterAwal;

            // Tampilkan preview HANYA JIKA volume >= 0 dan kolom input tidak kosong
            if (volume < 0 || isNaN(volume) || strAkhir === '') {
                document.getElementById('preview_tagihan').classList.add('hidden');
                return;
            }

            // Ambil data tarif dari database (Opsi B)
            const tarif0_5 = {{ $tarif->tarif_0_5 ?? 10000 }};
            const tarif6_10 = {{ $tarif->tarif_6_10 ?? 10000 }};
            const tarif11_20 = {{ $tarif->tarif_11_20 ?? 2200 }};
            const tarif21_30 = {{ $tarif->tarif_21_30 ?? 2500 }};
            const tarifAtas30 = {{ $tarif->tarif_atas_30 ?? 3000 }};
            const biayaBebanFix = {{ $tarif->biaya_beban ?? 5000 }};

            let biayaPemakaian = 0;

            // Rumus blok progresif Opsi B
            if (volume > 0) {
                biayaPemakaian += tarif0_5;
            }
            if (volume > 5) {
                biayaPemakaian += tarif6_10;
            }
            if (volume > 10) {
                let sisa = Math.min(volume, 20) - 10;
                biayaPemakaian += sisa * tarif11_20;
            }
            if (volume > 20) {
                let sisa = Math.min(volume, 30) - 20;
                biayaPemakaian += sisa * tarif21_30;
            }
            if (volume > 30) {
                let sisa = volume - 30;
                biayaPemakaian += sisa * tarifAtas30;
            }

            const total = biayaPemakaian + biayaBebanFix;

            document.getElementById('prev_volume').textContent = volume.toFixed(2).replace('.', ',') + ' m³';
            document.getElementById('prev_beban').textContent = 'Rp ' + biayaBebanFix.toLocaleString('id-ID');
            document.getElementById('prev_biaya').textContent = 'Rp ' + biayaPemakaian.toLocaleString('id-ID');
            document.getElementById('prev_total').textContent = 'Rp ' + total.toLocaleString('id-ID');

            document.getElementById('preview_tagihan').classList.remove('hidden');
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
