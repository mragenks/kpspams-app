@extends('layouts.admin')
@section('title', $jenis === 'masuk' ? 'Tambah Pemasukan' : 'Tambah Pengeluaran')

@section('content')
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow p-4 sm:p-6">
        <h2 class="text-lg font-semibold text-gray-700 mb-6">
            {{ $jenis === 'masuk' ? '💰 Tambah Pemasukan' : '💸 Tambah Pengeluaran' }}
        </h2>

        <form action="{{ route('admin.arus-kas.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <input type="hidden" name="jenis_transaksi" value="{{ $jenis }}">

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Akun Kas <span class="text-red-500">*</span>
                </label>
                <select name="akun_kas_id"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white transition {{ $errors->has('akun_kas_id') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                    <option value="">-- Pilih Akun Kas --</option>
                    @foreach ($akunKasList as $ak)
                        <option value="{{ $ak->id }}" {{ old('akun_kas_id') == $ak->id ? 'selected' : '' }}>
                            {{ $ak->nama_akun }}
                            (Saldo: Rp {{ number_format($ak->saldo, 0, ',', '.') }})
                        </option>
                    @endforeach
                </select>
                @error('akun_kas_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nominal <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-2 text-sm text-gray-500">Rp</span>
                    <input type="text" name="nominal" value="{{ old('nominal') }}" placeholder="Contoh: 50000"
                        class="w-full border rounded-lg pl-10 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition {{ $errors->has('nominal') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                </div>
                @error('nominal')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Keterangan <span class="text-red-500">*</span>
                </label>
                <input type="text" name="keterangan" value="{{ old('keterangan') }}"
                    placeholder="Contoh: Pembelian pipa PVC, Iuran warga Rt 01..."
                    class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition {{ $errors->has('keterangan') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                @error('keterangan')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Foto Bukti <span class="text-gray-400 text-xs">(Opsional, Maks 5MB)</span>
                </label>
                <input type="file" name="foto_bukti" accept="image/*"
                    class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white file:mr-4 file:py-1 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition {{ $errors->has('foto_bukti') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}"
                    onchange="previewFoto(this, 'preview_bukti')">
                <p class="text-xs text-gray-400 mt-1">Format: JPG, JPEG, PNG. Maksimal 5MB</p>
                @error('foto_bukti')
                    <p class="text-red-500 text-xs mt-1 font-semibold">⚠️ {{ $message }}</p>
                @enderror
                <div id="preview_bukti" class="hidden mt-3">
                    <img src="" alt="Preview" class="w-40 h-40 object-cover rounded-lg border shadow-sm">
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Tanggal <span class="text-red-500">*</span>
                </label>
                <input type="date" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}"
                    readonly
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-500 cursor-not-allowed pointer-events-none">
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <button type="submit"
                    class="w-full sm:w-auto text-center {{ $jenis === 'masuk' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700' }} text-white px-6 py-2.5 rounded-lg text-sm font-medium transition">
                    Simpan
                </button>
                <a href="{{ route('admin.arus-kas.index') }}"
                    class="w-full sm:w-auto text-center bg-gray-500 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-600 transition">
                    Batal
                </a>
            </div>
        </form>
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

    @if ($errors->has('foto_bukti'))
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Mengunggah Bukti!',
                    text: '{{ $errors->first('foto_bukti') }}',
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'Pilih Foto Ulang'
                });
            });
        </script>
    @endif
@endsection
