@extends('layouts.admin')
@section('title', 'Tambah Aset')

@section('content')
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow p-4 sm:p-6">
        <h2 class="text-lg font-semibold text-gray-700 mb-6">Tambah Aset Baru</h2>

        <form action="{{ route('admin.inventaris.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nama Barang <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nama_barang" value="{{ old('nama_barang') }}"
                    placeholder="Contoh: Mesin Pompa Air, Pipa PVC 3 inch..."
                    class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition {{ $errors->has('nama_barang') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                @error('nama_barang')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Jenis Aset <span class="text-red-500">*</span>
                    </label>
                    <select name="jenis_aset"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="tetap" {{ old('jenis_aset') === 'tetap' ? 'selected' : '' }}>
                            Aset Tetap (Pompa, Bangunan, dll)
                        </option>
                        <option value="tidak_tetap" {{ old('jenis_aset') === 'tidak_tetap' ? 'selected' : '' }}>
                            Aset Tidak Tetap (Pipa, Kaporit, dll)
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Kondisi <span class="text-red-500">*</span>
                    </label>
                    <select name="kondisi"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="baik" {{ old('kondisi') === 'baik' ? 'selected' : '' }}>Baik</option>
                        <option value="perlu_perbaikan" {{ old('kondisi') === 'perlu_perbaikan' ? 'selected' : '' }}>Perlu
                            Perbaikan</option>
                        <option value="rusak" {{ old('kondisi') === 'rusak' ? 'selected' : '' }}>Rusak</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Jumlah <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="jumlah" value="{{ old('jumlah', 1) }}" min="1"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition {{ $errors->has('jumlah') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                    @error('jumlah')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Perolehan <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_perolehan" value="{{ old('tanggal_perolehan', date('Y-m-d')) }}" readonly
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-500 cursor-not-allowed pointer-events-none">
                    @error('tanggal_perolehan')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
                    <input type="text" name="lokasi" value="{{ old('lokasi') }}"
                        placeholder="Contoh: Rumah Pompa RT 04, Gudang Desa..."
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Batas Stok Menipis (Peringatan) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="stok_minimum" value="{{ old('stok_minimum', 5) }}" min="0"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition {{ $errors->has('stok_minimum') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                    @error('stok_minimum')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                <textarea name="keterangan" rows="3" placeholder="Deskripsi tambahan (opsional)"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('keterangan') }}</textarea>
            </div>

            <!-- Checkbox Catat Pengeluaran Kas -->
            <div class="mb-4 bg-gray-50 border border-gray-200 rounded-lg p-4">
                <div class="flex items-center">
                    <input type="checkbox" name="catat_pengeluaran" id="catat_pengeluaran" value="1"
                        {{ old('catat_pengeluaran') ? 'checked' : '' }}
                        class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                        onchange="togglePengeluaranSection()">
                    <label for="catat_pengeluaran" class="ml-2 block text-sm font-medium text-gray-900 cursor-pointer select-none">
                        Catat langsung sebagai Pengeluaran Kas (Arus Kas)
                    </label>
                </div>

                <!-- Section Pengeluaran Kas (Dinamis) -->
                <div id="section_pengeluaran" class="{{ old('catat_pengeluaran') ? '' : 'hidden' }} mt-4 pt-4 border-t border-gray-200 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Akun Kas <span class="text-red-500">*</span>
                            </label>
                            <select name="akun_kas_id"
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
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Harga Perolehan / Nominal Pengeluaran <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="harga_perolehan" value="{{ old('harga_perolehan') }}" min="0"
                                placeholder="Contoh: 150000"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">
                            @error('harga_perolehan')
                                <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Foto Bukti Pembelian <span class="text-gray-400 text-xs">(Opsional, Maks 5MB)</span>
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
                </div>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-6">
                <p class="text-xs text-blue-700">
                    💡 Kode barang akan dibuat otomatis: <strong>AT-XXX</strong> untuk aset tetap,
                    <strong>ATT-XXX</strong> untuk aset tidak tetap.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <button type="submit"
                    class="w-full sm:w-auto text-center bg-blue-600 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                    Simpan
                </button>
                <a href="{{ route('admin.inventaris.index') }}"
                    class="w-full sm:w-auto text-center bg-gray-500 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-600 transition">
                    Batal
                </a>
            </div>
        </form>
    </div>

    <script>
        function togglePengeluaranSection() {
            const checkbox = document.getElementById('catat_pengeluaran');
            const section = document.getElementById('section_pengeluaran');
            if (checkbox.checked) {
                section.classList.remove('hidden');
            } else {
                section.classList.add('hidden');
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
