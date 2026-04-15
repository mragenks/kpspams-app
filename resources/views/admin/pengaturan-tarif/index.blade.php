@extends('layouts.admin')
@section('title', 'Pengaturan Tarif Air')

@section('content')
    <div class="max-w-3xl mx-auto">

        {{-- JIKA BELUM DIBUKA GEMBOKNYA --}}
        @if (!session('tarif_unlocked'))
            <div class="max-w-md mx-auto mt-10">
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
                    <div class="bg-blue-600 p-6 flex flex-col items-center justify-center text-white">
                        <svg class="w-16 h-16 mb-2 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <h2 class="text-xl font-bold">Zona Terkunci</h2>
                        <p class="text-sm text-blue-100 text-center mt-1">Area ini bersifat sensitif. Silakan masukkan
                            password akun Anda untuk melanjutkan.</p>
                    </div>

                    <div class="p-6">
                        <form action="{{ route('admin.pengaturan-tarif.unlock') }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Password Akun</label>
                                <input type="password" name="password" required autofocus
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="••••••••">
                            </div>
                            <button type="submit"
                                class="w-full bg-gray-800 text-white font-bold py-2.5 rounded-lg hover:bg-gray-900 transition flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                </svg>
                                Buka Gembok
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- JIKA SUDAH BERHASIL DIBUKA --}}
        @else
            <div class="flex justify-between items-end mb-4">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 flex-1 mr-4">
                    <p class="text-blue-700 text-xs leading-relaxed">
                        Perubahan tarif di bawah ini akan langsung berlaku pada saat Admin melakukan <b>Pencatatan Meter
                            Baru</b>.
                    </p>
                </div>

                {{-- Tombol untuk mengunci manual --}}
                <form action="{{ route('admin.pengaturan-tarif.lock') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="bg-gray-600 hover:bg-gray-700 text-white text-xs font-semibold px-4 py-2.5 rounded-lg transition flex items-center gap-1.5 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        Kunci Kembali
                    </button>
                </form>
            </div>

            <div class="bg-white rounded-lg shadow overflow-hidden border border-gray-100 border-t-4 border-t-green-500">
                <div class="p-4 sm:p-6 border-b flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-gray-700">Komponen Biaya & Tarif Air</h2>
                    <span
                        class="bg-green-100 text-green-700 text-[10px] font-bold px-2 py-1 rounded uppercase tracking-wide">Status:
                        Terbuka</span>
                </div>

                <form action="{{ route('admin.pengaturan-tarif.update') }}" method="POST" class="p-4 sm:p-6">
                    @csrf
                    <div class="space-y-6">
                        {{-- Biaya Beban --}}
                        <div>
                            <h4 class="text-sm font-bold text-gray-800 mb-3 border-b pb-2">1. Biaya Tetap Bulanan</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">Biaya Beban
                                        (Abodemen)</label>
                                    <div class="relative">
                                        <span
                                            class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500 font-medium">Rp</span>
                                        <input type="number" name="biaya_beban" value="{{ $tarif->biaya_beban }}" required
                                            class="w-full border border-gray-300 rounded-md pl-10 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Tarif Progresif --}}
                        <div>
                            <h4 class="text-sm font-bold text-gray-800 mb-3 border-b pb-2">2. Tarif Pemakaian Air
                                (Progresif)</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">Tarif Batas Bawah (0 - 10
                                        m³)</label>
                                    <div class="relative">
                                        <span
                                            class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500 font-medium">Rp</span>
                                        <input type="number" name="tarif_batas_bawah"
                                            value="{{ $tarif->tarif_batas_bawah }}" required
                                            class="w-full border border-gray-300 rounded-md pl-10 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">Tarif 11 - 20 m³ (Per
                                        m³)</label>
                                    <div class="relative">
                                        <span
                                            class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500 font-medium">Rp</span>
                                        <input type="number" name="tarif_11_20" value="{{ $tarif->tarif_11_20 }}" required
                                            class="w-full border border-gray-300 rounded-md pl-10 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">Tarif 21 - 30 m³ (Per
                                        m³)</label>
                                    <div class="relative">
                                        <span
                                            class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500 font-medium">Rp</span>
                                        <input type="number" name="tarif_21_30" value="{{ $tarif->tarif_21_30 }}" required
                                            class="w-full border border-gray-300 rounded-md pl-10 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">Tarif > 30 m³ (Per
                                        m³)</label>
                                    <div class="relative">
                                        <span
                                            class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500 font-medium">Rp</span>
                                        <input type="number" name="tarif_atas_30" value="{{ $tarif->tarif_atas_30 }}"
                                            required
                                            class="w-full border border-gray-300 rounded-md pl-10 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 border-t pt-4 flex justify-end">
                        <button type="submit"
                            class="bg-blue-600 text-white px-8 py-2.5 rounded-lg text-sm font-medium hover:bg-blue-700 transition shadow-sm">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        @endif
    </div>
@endsection
