@extends('layouts.admin')
@section('title', 'Detail Pelanggan')

@section('content')
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-lg font-semibold text-gray-700">Detail Pelanggan</h2>
            <a href="{{ route('admin.pelanggan.index') }}" class="text-sm text-blue-600 hover:underline">← Kembali</a>
        </div>

        <div class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-400 uppercase">Kode Pelanggan</p>
                    <p class="font-semibold text-blue-600">{{ $pelanggan->kode_pelanggan }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase">Status</p>
                    @if ($pelanggan->status === 'aktif')
                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs">Aktif</span>
                    @else
                        <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs">Non-aktif</span>
                    @endif
                </div>
            </div>

            <div>
                <p class="text-xs text-gray-400 uppercase">Nama Pelanggan</p>
                <p class="font-medium">{{ $pelanggan->nama_pelanggan }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-400 uppercase">NIK</p>
                    <p>{{ $pelanggan->nik ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase">No. HP</p>
                    <p>{{ $pelanggan->no_hp ?? '-' }}</p>
                </div>
            </div>

            <div>
                <p class="text-xs text-gray-400 uppercase">Alamat</p>
                <p>{{ $pelanggan->alamat ?? '-' }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-400 uppercase">Meteran Awal</p>
                    <p>{{ number_format($pelanggan->meteran_awal, 2) }} m³</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase">Username Login</p>
                    <p class="font-mono text-sm bg-gray-100 px-2 py-1 rounded">{{ $pelanggan->user->username }}</p>
                </div>
            </div>
        </div>

        <div class="mt-6 pt-4 border-t flex gap-3">
            <a href="{{ route('admin.pelanggan.edit', $pelanggan) }}"
                class="bg-yellow-500 text-white px-4 py-2 rounded-lg text-sm hover:bg-yellow-600 transition">
                Edit Data
            </a>
        </div>
    </div>
@endsection
