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

                <div class="flex justify-end">
                    <button type="submit"
                        class="bg-yellow-500 hover:bg-yellow-600 text-white font-medium py-2 px-6 rounded-lg transition-colors">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
