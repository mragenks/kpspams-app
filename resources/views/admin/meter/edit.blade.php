@extends('layouts.admin')

@section('title', 'Edit Pencatatan Meter')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-800">Edit Meteran Air</h2>
            <a href="{{ route('admin.meter.show', $meter->id) }}"
                class="text-gray-500 hover:text-gray-700 font-medium text-sm flex items-center">
                &larr; Kembali ke Detail
            </a>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-t-4 border-yellow-500">
            <div class="mb-6 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                <h4 class="font-bold text-yellow-800 mb-1">Informasi Pelanggan</h4>
                <p class="text-sm text-yellow-700"><strong>Nama:</strong> {{ $meter->pelanggan->nama_pelanggan }}
                    ({{ $meter->pelanggan->kode_pelanggan }})</p>
                <p class="text-sm text-yellow-700"><strong>Periode:</strong>
                    {{ \Carbon\Carbon::create(null, $meter->bulan_periode, 1)->isoFormat('MMMM') }}
                    {{ $meter->tahun_periode }}</p>
                <p class="text-xs text-yellow-600 mt-2">*Perhatian: Mengubah nilai meter akhir akan otomatis menghitung
                    ulang total tagihan pelanggan ini.</p>
            </div>

            {{-- FORM UTAMA (UPDATE) --}}
            <form action="{{ route('admin.meter.update', $meter->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Meter Awal (Bulan Lalu)</label>
                        <input type="text" value="{{ $meter->meter_awal }}" readonly
                            class="w-full border-gray-300 bg-gray-100 rounded-lg shadow-sm px-4 py-2 cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Meter Akhir (Perbaikan) <span
                                class="text-red-500">*</span></label>
                        <input type="number" name="meter_akhir" value="{{ old('meter_akhir', $meter->meter_akhir) }}"
                            required min="{{ $meter->meter_awal }}"
                            class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-2 focus:ring-yellow-500 focus:border-yellow-500">
                        @error('meter_akhir')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Update Foto Meteran (Opsional)</label>
                    <input type="file" name="foto_meteran" accept="image/*"
                        class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-2 text-sm">
                    <p class="text-xs text-gray-500 mt-1">Biarkan kosong jika foto tidak perlu diubah.</p>
                    @error('foto_meteran')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>

                {{-- AREA TOMBOL BAWAH --}}
                <div class="flex justify-between items-center pt-4 mt-6 border-t border-gray-100">

                    {{-- 1. Tombol Hapus (Kiri) --}}
                    <button type="button" onclick="confirmDelete()"
                        class="text-red-600 bg-red-50 hover:bg-red-100 px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                            </path>
                        </svg>
                        Hapus Pencatatan
                    </button>

                    {{-- 2. Tombol Simpan (Kanan) --}}
                    <button type="submit"
                        class="bg-yellow-500 hover:bg-yellow-600 text-white font-medium py-2 px-6 rounded-lg transition-colors">
                        Simpan Perubahan
                    </button>

                </div>
            </form>
            {{-- AKHIR FORM UTAMA --}}

        </div>
    </div>

    {{-- 3. Form Delete Tersembunyi (Di Luar Form Utama) --}}
    <form id="delete-form-{{ $meter->id }}" action="{{ route('admin.meter.destroy', $meter->id) }}" method="POST"
        class="hidden">
        @csrf
        @method('DELETE')
    </form>

    {{-- 4. Script SweetAlert --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDelete() {
            Swal.fire({
                title: 'Hapus Data Pencatatan?',
                text: "Data pencatatan meter beserta tagihan air bulan ini akan ikut terhapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Hapus Permanen!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-{{ $meter->id }}').submit();
                }
            });
        }
    </script>
@endsection
