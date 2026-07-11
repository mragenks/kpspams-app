@extends('layouts.admin')
@section('title', 'Edit Akun Kas')

@section('content')
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow p-4 sm:p-6">
        <h2 class="text-lg font-semibold text-gray-700 mb-6">Edit Akun Kas</h2>

        <form action="{{ route('admin.akun-kas.update', $akunKas) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nama Akun <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nama_akun" value="{{ old('nama_akun', $akunKas->nama_akun) }}"
                    class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition {{ $errors->has('nama_akun') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                @error('nama_akun')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Jenis Akun <span class="text-red-500">*</span>
                </label>
                <select name="jenis_akun"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="tunai" {{ $akunKas->jenis_akun === 'tunai' ? 'selected' : '' }}>Tunai</option>
                    <option value="bank" {{ $akunKas->jenis_akun === 'bank' ? 'selected' : '' }}>Bank</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Saldo Saat Ini</label>
                <div class="relative">
                    <span class="absolute left-3 top-2 text-sm text-gray-500">Rp</span>
                    <input type="text" disabled value="{{ number_format($akunKas->saldo, 0, ',', '.') }}"
                        class="w-full border border-gray-200 rounded-lg pl-10 pr-3 py-2 text-sm bg-gray-50 text-gray-500">
                </div>
                <p class="text-xs text-gray-400 mt-1">Saldo hanya berubah melalui transaksi arus kas</p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                <textarea name="keterangan" rows="3"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('keterangan', $akunKas->keterangan) }}</textarea>
            </div>

            <div class="mb-6">
                <label class="flex items-center gap-2 cursor-pointer w-fit">
                    <input type="checkbox" name="is_active" value="1" {{ $akunKas->is_active ? 'checked' : '' }}
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4">
                    <span class="text-sm font-medium text-gray-700">Akun Aktif</span>
                </label>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <button type="submit"
                    class="w-full sm:w-auto text-center bg-blue-600 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                    Update
                </button>
                <a href="{{ route('admin.akun-kas.index') }}"
                    class="w-full sm:w-auto text-center bg-gray-500 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-600 transition">
                    Batal
                </a>
            </div>
        </form>

        <div class="mt-8 pt-6 border-t border-gray-200">
            <h3 class="text-sm font-semibold text-red-600 mb-2">Hapus Akun Kas</h3>
            <p class="text-sm text-gray-500 mb-4">Tindakan ini akan menghapus akun kas dari sistem. Pastikan tidak ada data
                arus kas atau pembayaran yang masih menggunakan akun ini.</p>

            {{-- MODIFIKASI: Menambahkan ID unik pada form dan mengganti type="submit" menjadi type="button" pemicu JS --}}
            <form id="delete-form-{{ $akunKas->id }}" action="{{ route('admin.akun-kas.destroy', $akunKas) }}"
                method="POST">
                @csrf
                @method('DELETE')
                <button type="button" onclick="confirmDeleteAccount()"
                    class="w-full sm:w-auto text-center bg-red-600 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-red-700 transition">
                    Hapus Akun
                </button>
            </form>
        </div>
    </div>

    {{-- MODIFIKASI: Penambahan Library CDN SweetAlert2 dan Skrip Interseptor Form --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDeleteAccount() {
            Swal.fire({
                title: 'Hapus Akun Kas?',
                text: "Akun kas beserta riwayat saldo di dalamnya akan ikut terhapus permanen dari pembukuan organisasi!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Hapus Permanen!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Jalankan submit form jika tombol konfirmasi "Ya" ditekan
                    document.getElementById('delete-form-{{ $akunKas->id }}').submit();
                }
            });
        }
    </script>
@endsection
