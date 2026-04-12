@extends('layouts.admin')
@section('title', 'Manajemen Saldo Pelanggan')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Form Top Up --}}
        <div class="md:col-span-1">
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Top Up Saldo</h3>
                <form action="{{ route('admin.deposit.store') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Pelanggan</label>
                        <select name="pelanggan_id" required class="w-full border-gray-300 rounded-lg text-sm">
                            <option value="">-- Cari Pelanggan --</option>
                            @foreach ($pelanggan as $p)
                                <option value="{{ $p->id }}">{{ $p->kode_pelanggan }} - {{ $p->nama_pelanggan }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Masuk ke Kas</label>
                        <select name="akun_kas_id" required class="w-full border-gray-300 rounded-lg text-sm">
                            @foreach ($akunKas as $ak)
                                <option value="{{ $ak->id }}">{{ $ak->nama_akun }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nominal (Rp)</label>
                        <input type="number" name="nominal" required min="1000"
                            class="w-full border-gray-300 rounded-lg text-sm" placeholder="Contoh: 50000">
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                        <input type="text" name="keterangan" class="w-full border-gray-300 rounded-lg text-sm"
                            placeholder="Opsional...">
                    </div>
                    <button type="submit"
                        class="w-full bg-blue-600 text-white py-2 rounded-lg font-bold hover:bg-blue-700 transition">
                        Simpan Deposit
                    </button>
                </form>
            </div>
        </div>

        {{-- Daftar Saldo --}}
        <div class="md:col-span-2">
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-bold text-gray-800">Data Saldo Pelanggan</h3>
                </div>
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                        <tr>
                            <th class="px-6 py-3">Pelanggan</th>
                            <th class="px-6 py-3 text-right">Saldo Saat Ini</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach ($pelanggan as $p)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-800">{{ $p->nama_pelanggan }}</div>
                                    <div class="text-xs text-blue-600">{{ $p->kode_pelanggan }}</div>
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
        </div>
    </div>
@endsection
