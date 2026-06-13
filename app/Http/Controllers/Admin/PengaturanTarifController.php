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
        $request->validate(['password' => 'required'], [
            'password.required' => 'Password wajib diisi untuk membuka pengaturan!'
        ]);
        
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
        ], [
            'biaya_beban.required'   => 'Biaya beban wajib diisi!',
            'biaya_beban.numeric'    => 'Biaya beban harus berupa angka!',
            'biaya_beban.min'        => 'Biaya beban tidak boleh kurang dari 0!',
            'tarif_0_5.required'     => 'Tarif 0-5 m³ wajib diisi!',
            'tarif_0_5.numeric'      => 'Tarif 0-5 m³ harus berupa angka!',
            'tarif_0_5.min'          => 'Tarif 0-5 m³ tidak boleh kurang dari 0!',
            'tarif_6_10.required'    => 'Tarif 6-10 m³ wajib diisi!',
            'tarif_6_10.numeric'     => 'Tarif 6-10 m³ harus berupa angka!',
            'tarif_6_10.min'         => 'Tarif 6-10 m³ tidak boleh kurang dari 0!',
            'tarif_11_20.required'   => 'Tarif 11-20 m³ wajib diisi!',
            'tarif_11_20.numeric'    => 'Tarif 11-20 m³ harus berupa angka!',
            'tarif_11_20.min'        => 'Tarif 11-20 m³ tidak boleh kurang dari 0!',
            'tarif_21_30.required'   => 'Tarif 21-30 m³ wajib diisi!',
            'tarif_21_30.numeric'    => 'Tarif 21-30 m³ harus berupa angka!',
            'tarif_21_30.min'        => 'Tarif 21-30 m³ tidak boleh kurang dari 0!',
            'tarif_atas_30.required' => 'Tarif > 30 m³ wajib diisi!',
            'tarif_atas_30.numeric'  => 'Tarif > 30 m³ harus berupa angka!',
            'tarif_atas_30.min'      => 'Tarif > 30 m³ tidak boleh kurang dari 0!',
        ]);

        $tarif = PengaturanTarif::first();
        $tarif->update($request->all());

        session()->forget('tarif_unlocked');

        return redirect()->route('admin.pengaturan-tarif.index')
            ->with('success', 'Pengaturan tarif berhasil diperbarui dan dikunci kembali secara otomatis.');
    }
}
