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

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tahun <span
                            class="text-red-500">*</span></label>
                    <select name="tahun_periode" id="tahun_periode"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        @foreach (range(date('Y'), 2020) as $t)
                            <option value="{{ $t }}"
                                {{ old('tahun_periode', date('Y')) == $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bulan <span
                            class="text-red-500">*</span></label>
                    <select name="bulan_periode" id="bulan_periode"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        @foreach (range(1, 12) as $b)
                            <option value="{{ $b }}"
                                {{ old('bulan_periode', date('n')) == $b ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($b)->isoFormat('MMMM') }}
                            </option>
                        @endforeach
                    </select>
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
            <div class="mb-4">
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

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pencatatan <span
                        class="text-red-500">*</span></label>
                <input type="date" name="tanggal_pencatatan" value="{{ old('tanggal_pencatatan', date('Y-m-d')) }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            </div>

            {{-- Preview Tagihan --}}
            <div id="preview_tagihan" class="hidden bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h3 class="text-sm font-semibold text-blue-800 mb-3 border-b border-blue-200 pb-2">Pratinjau Tagihan:</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>Volume Pemakaian:</span>
                        <span id="prev_volume" class="font-medium text-gray-800">0 m³</span>
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
        // Event Listener saat user mengubah dropdown tahun
        document.getElementById('tahun_periode').addEventListener('change', filterBulan);

        // Modifikasi loadMeterAwal untuk memicu filterBulan
        function loadMeterAwal(pelangganId) {
            const select = document.getElementById('pelanggan_id');
            const selected = select.options[select.selectedIndex];

            // Tampilkan Meter Awal
            const meteranAwal = selected.getAttribute('data-meteran');
            document.getElementById('meter_awal_display').value = meteranAwal ?? 0;

            // Kunci bulan & tahun yang kadaluarsa
            filterBulan();

            // Hitung preview (jika user menukar pelanggan saat angka meter akhir sudah terisi)
            hitungPreview();
        }

        // Fungsi Cerdas: Disable dropdown berdasarkan data terakhir
        function filterBulan() {
            const selectPelanggan = document.getElementById('pelanggan_id');
            const selectedOpt = selectPelanggan.options[selectPelanggan.selectedIndex];

            // Jika belum ada pelanggan yang dipilih, buka semua opsi
            if (!selectedOpt || !selectedOpt.value) return;

            const bulanTerakhir = parseInt(selectedOpt.getAttribute('data-bulan')) || 0;
            const tahunTerakhir = parseInt(selectedOpt.getAttribute('data-tahun')) || 0;

            const selectBulan = document.getElementById('bulan_periode');
            const selectTahun = document.getElementById('tahun_periode');
            const tahunDipilih = parseInt(selectTahun.value);

            // 1. Kunci Tahun yang <= Tahun Terakhir, KECUALI tahun tersebut masih punya sisa bulan
            Array.from(selectTahun.options).forEach(opt => {
                const nilaiTahun = parseInt(opt.value);
                // Jika tahun tsb < tahun terakhir OR (tahun tsb = tahun terakhir TAPI bulan terakhir = 12)
                if (nilaiTahun < tahunTerakhir || (nilaiTahun === tahunTerakhir && bulanTerakhir === 12)) {
                    opt.disabled = true;
                    opt.classList.add('text-gray-300', 'bg-gray-100');
                } else {
                    opt.disabled = false;
                    opt.classList.remove('text-gray-300', 'bg-gray-100');
                }
            });

            // 2. Kunci Bulan jika Tahun yang dipilih sama dengan Tahun Terakhir pencatatan
            Array.from(selectBulan.options).forEach(opt => {
                const nilaiBulan = parseInt(opt.value);

                if (tahunDipilih === tahunTerakhir && nilaiBulan <= bulanTerakhir) {
                    opt.disabled = true;
                    opt.classList.add('text-gray-300', 'bg-gray-100');
                } else {
                    opt.disabled = false;
                    opt.classList.remove('text-gray-300', 'bg-gray-100');
                }
            });

            // 3. Auto-Select: Jika opsi yang sedang terpilih ternyata 'disabled', lompat ke opsi valid pertama
            if (selectTahun.options[selectTahun.selectedIndex].disabled) {
                const firstValidTahun = Array.from(selectTahun.options).find(opt => !opt.disabled);
                if (firstValidTahun) selectTahun.value = firstValidTahun.value;
                // Karena tahun berubah otomatis, kita perlu panggil ulang logika disable bulannya
                filterBulan();
                return;
            }

            if (selectBulan.options[selectBulan.selectedIndex].disabled) {
                const firstValidBulan = Array.from(selectBulan.options).find(opt => !opt.disabled);
                if (firstValidBulan) selectBulan.value = firstValidBulan.value;
            }
        }

        // Jalankan filter saat halaman pertama kali dimuat (untuk skenario old() session saat validasi error)
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('pelanggan_id').value !== "") {
                filterBulan();
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
