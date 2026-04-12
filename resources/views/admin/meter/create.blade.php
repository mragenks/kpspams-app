@extends('layouts.admin')
@section('title', 'Catat Meter Air')

@section('content')
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow p-4 sm:p-6">
        <h2 class="text-lg font-semibold text-gray-700 mb-6">Catat Meter Air</h2>

        <form action="{{ route('admin.meter.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Pelanggan <span
                        class="text-red-500">*</span></label>
                <select name="pelanggan_id" id="pelanggan_id"
                    class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition bg-white {{ $errors->has('pelanggan_id') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}"
                    onchange="loadMeterAwal(this.value)">
                    <option value="" data-bulan="0" data-tahun="0">-- Pilih Pelanggan --</option>
                    @foreach ($pelanggan as $p)
                        <option value="{{ $p->id }}" data-meteran="{{ $p->meter_terakhir }}"
                            data-bulan="{{ $p->bulan_terakhir }}" data-tahun="{{ $p->tahun_terakhir }}"
                            {{ old('pelanggan_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->kode_pelanggan }} - {{ $p->nama_pelanggan }}
                        </option>
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
                        // Menyiapkan format YYYY-MM untuk old input
                        $oldBulan = old('bulan_periode', date('n'));
                        $oldTahun = old('tahun_periode', date('Y'));
                        $oldPeriode = $oldTahun . '-' . str_pad($oldBulan, 2, '0', STR_PAD_LEFT);
                    @endphp

                    {{-- Input Tipe Month (Gabungan Bulan & Tahun) --}}
                    <input type="month" id="periode_input" value="{{ $oldPeriode }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                        onchange="updateHiddenPeriode()">

                    {{-- Hidden Input untuk dikirim ke Controller --}}
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
                        <span class="font-medium text-gray-800">Rp 5.000</span>
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

    <script>
        // Sinkronisasi Input "Month" ke Hidden "Tahun" & "Bulan"
        function updateHiddenPeriode() {
            const val = document.getElementById('periode_input').value;
            if (val) {
                const parts = val.split('-'); // Pecah '2026-04' menjadi ['2026', '04']
                document.getElementById('tahun_periode').value = parts[0];
                document.getElementById('bulan_periode').value = parseInt(parts[1], 10); // Ubah '04' jadi 4
            }
        }

        // Modifikasi loadMeterAwal dengan fitur Cerdas HTML5 'min'
        function loadMeterAwal(pelangganId) {
            const select = document.getElementById('pelanggan_id');
            const selected = select.options[select.selectedIndex];

            // Jika dikembalikan ke '-- Pilih Pelanggan --'
            if (!selected || !selected.value) {
                document.getElementById('periode_input').removeAttribute('min');
                document.getElementById('meter_awal_display').value = '';
                return;
            }

            // Tampilkan Meter Awal
            const meteranAwal = selected.getAttribute('data-meteran');
            document.getElementById('meter_awal_display').value = meteranAwal ?? 0;

            // Kunci bulan & tahun menggunakan HTML5 Attribute 'min'
            const bulanTerakhir = parseInt(selected.getAttribute('data-bulan')) || 0;
            const tahunTerakhir = parseInt(selected.getAttribute('data-tahun')) || 0;
            const periodeInput = document.getElementById('periode_input');

            if (bulanTerakhir > 0 && tahunTerakhir > 0) {
                // Hitung bulan tagihan selanjutnya yang valid
                let nextBulan = bulanTerakhir + 1;
                let nextTahun = tahunTerakhir;
                if (nextBulan > 12) {
                    nextBulan = 1;
                    nextTahun++;
                }

                // Ubah menjadi format YYYY-MM
                const minBulanStr = nextBulan.toString().padStart(2, '0');
                const minPeriode = `${nextTahun}-${minBulanStr}`;

                // Terapkan atribut 'min' (otomatis mendisable bulan sebelumnya di UI kalender bawaan HP/Browser)
                periodeInput.min = minPeriode;

                // Jika input saat ini (old value) ternyata sudah kadaluarsa, majukan paksa ke minPeriode
                if (periodeInput.value < minPeriode) {
                    periodeInput.value = minPeriode;
                    updateHiddenPeriode();
                }
            } else {
                periodeInput.removeAttribute('min');
            }

            hitungPreview();
        }

        document.addEventListener('DOMContentLoaded', function() {
            updateHiddenPeriode();
            if (document.getElementById('pelanggan_id').value !== "") {
                loadMeterAwal(document.getElementById('pelanggan_id').value);
            }
        });

        function hitungPreview() {
            const meterAwal = parseFloat(document.getElementById('meter_awal_display').value) || 0;
            const meterAkhir = parseFloat(document.getElementById('meter_akhir').value) || 0;
            const volume = meterAkhir - meterAwal;

            if (volume <= 0) {
                document.getElementById('preview_tagihan').classList.add('hidden');
                return;
            }

            let biayaPemakaian = 0;
            if (volume <= 10) {
                biayaPemakaian = 10000;
            } else if (volume <= 20) {
                biayaPemakaian = 10000 + ((volume - 10) * 2200);
            } else if (volume <= 30) {
                biayaPemakaian = 10000 + (10 * 2200) + ((volume - 20) * 2500);
            } else {
                biayaPemakaian = 10000 + (10 * 2200) + (10 * 2500) + ((volume - 30) * 3000);
            }

            const total = biayaPemakaian + 5000;

            document.getElementById('prev_volume').textContent = volume.toFixed(2) + ' m³';
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
