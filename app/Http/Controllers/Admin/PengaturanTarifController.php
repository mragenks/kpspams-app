<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PengaturanTarif;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PengaturanTarifController extends Controller
{
    public function index()
    {
        $tarif = PengaturanTarif::first();

        if (!$tarif) {
            $tarif = PengaturanTarif::create([
                'biaya_beban' => 5000,
                'tarif_0_5'   => 10000,
                'tarif_6_10'  => 10000,
                'tarif_11_20' => 2200,
                'tarif_21_30' => 2500,
                'tarif_atas_30' => 3000,
            ]);
        }

        return view('admin.pengaturan-tarif.index', compact('tarif'));
    }

    public function unlock(Request $request)
    {
        $request->validate(['password' => 'required']);
        if (Hash::check($request->password, auth()->user()->password)) {
            session(['tarif_unlocked' => true]);
            return back()->with('success', 'Gembok Pengaturan Tarif berhasil dibuka!');
        }
        return back()->with('error', 'Password salah! Tidak dapat membuka pengaturan.');
    }

    public function lock()
    {
        session()->forget('tarif_unlocked');
        return back()->with('success', 'Pengaturan Tarif telah dikunci kembali untuk keamanan.');
    }

    public function update(Request $request)
    {
        if (!session('tarif_unlocked')) {
            return back()->with('error', 'Akses ditolak! Anda harus membuka gembok terlebih dahulu.');
        }

        $request->validate([
            'biaya_beban'   => 'required|numeric|min:0',
            'tarif_0_5'     => 'required|numeric|min:0',
            'tarif_6_10'    => 'required|numeric|min:0',
            'tarif_11_20'   => 'required|numeric|min:0',
            'tarif_21_30'   => 'required|numeric|min:0',
            'tarif_atas_30' => 'required|numeric|min:0',
        ]);

        $tarif = PengaturanTarif::first();
        $tarif->update($request->all());

        session()->forget('tarif_unlocked');

        return redirect()->route('admin.pengaturan-tarif.index')
            ->with('success', 'Pengaturan tarif berhasil diperbarui dan dikunci kembali secara otomatis.');
    }
}
