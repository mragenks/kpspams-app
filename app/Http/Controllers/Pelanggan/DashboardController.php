<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\TagihanAir;

class DashboardController extends Controller
{
    public function index()
    {
        $pelanggan = auth()->user()->pelanggan;

        if (!$pelanggan) {
            abort(403, 'Data pelanggan tidak ditemukan.');
        }

        // Tagihan yang belum lunas
        $tagihanAktif = TagihanAir::where('pelanggan_id', $pelanggan->id)
            ->whereIn('status_bayar', ['belum_lunas', 'mencicil'])
            ->latest()
            ->first();

        // Riwayat tagihan dengan relasi pembayaran dan foto bukti
        $riwayatTagihan = TagihanAir::where('pelanggan_id', $pelanggan->id)
            ->with(['pencatatanMeter', 'pembayaran.akunKas'])
            ->latest()
            ->paginate(6);

        return view('pelanggan.dashboard', compact(
            'pelanggan',
            'tagihanAktif',
            'riwayatTagihan'
        ));
    }
}
