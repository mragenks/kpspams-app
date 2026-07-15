@extends('layouts.admin')
@section('title', 'Manajemen Saldo Pelanggan')

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
    <style>
        .ts-control {
            border: 1px solid #d1d5db;
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            box-shadow: none;
            font-size: 0.875rem;
        }

        .ts-control.focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
        }
    </style>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Form Top Up --}}
        <div class="md:col-span-1">
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Top Up Saldo</h3>
                <form action="{{ route('admin.deposit.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Pelanggan</label>
                        <select name="pelanggan_id" id="pelanggan_id" autocomplete="off"
                            placeholder="Ketik Kode atau Nama...">
                            <option value="">-- Cari Pelanggan --</option>
                            @foreach ($pelanggan as $p)
                                <option value="{{ $p->id }}" {{ old('pelanggan_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->kode_pelanggan }} - {{ $p->nama_pelanggan }}
                                </option>
                            @endforeach
                        </select>
                        @error('pelanggan_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Masuk ke Kas</label>
                        <select name="akun_kas_id"
                            class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                            @foreach ($akunKas as $ak)
                                <option value="{{ $ak->id }}" {{ old('akun_kas_id') == $ak->id ? 'selected' : '' }}>{{ $ak->nama_akun }}</option>
                            @endforeach
                        </select>
                        @error('akun_kas_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nominal (Rp)</label>
                        <input type="number" name="nominal" value="{{ old('nominal') }}"
                            class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Contoh: 50000">
                        @error('nominal')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                        <input type="text" name="keterangan" value="{{ old('keterangan') }}"
                            class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Opsional...">
                        @error('keterangan')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
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
                            <img src="" alt="Preview" class="w-40 h-40 object-cover rounded-lg border shadow-sm mx-auto">
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full bg-blue-600 text-white py-2.5 rounded-lg font-bold hover:bg-blue-700 transition shadow-sm">
                        Simpan Deposit
                    </button>
                </form>
            </div>
        </div>

        {{-- Daftar Saldo --}}
        <div class="md:col-span-2">
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="p-6 border-b flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-800">Data Saldo Pelanggan</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 text-gray-600 uppercase text-[10px] tracking-wider border-b">
                            <tr>
                                <th class="px-6 py-4">Pelanggan</th>
                                <th class="px-6 py-4 text-right">Saldo Saat Ini</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach ($pelangganPaginated as $p)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-800">{{ $p->nama_pelanggan }}</div>
                                        <div class="text-xs text-blue-600 font-mono">{{ $p->kode_pelanggan }}</div>
                                    </td>
                                    <td
                                        class="px-6 py-4 text-right font-mono font-bold {{ $p->saldo > 0 ? 'text-green-600' : 'text-gray-400' }}">
                                        Rp {{ number_format($p->saldo, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Menampilkan Paginasi --}}
                @if ($pelangganPaginated->hasPages())
                    <div class="p-4 border-t">
                        {{ $pelangganPaginated->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Script JS Tom Select --}}
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new TomSelect("#pelanggan_id", {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                }
            });
        });

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
