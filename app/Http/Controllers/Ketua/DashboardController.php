<?php

namespace App\Http\Controllers\Ketua;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Models\TagihanAir;
use App\Models\AkunKas;
use App\Models\ArusKas;
use App\Models\Inventaris;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $bulan = date('n');
        $tahun = date('Y');

        $totalPelanggan = Pelanggan::where('status', 'aktif')->count();
        $tagihanBelumLunas = TagihanAir::whereIn('status_bayar', ['belum_lunas', 'mencicil'])->count();
        $totalSaldo = AkunKas::where('is_active', true)->sum('saldo');
        $totalPiutang = TagihanAir::whereIn('status_bayar', ['belum_lunas', 'mencicil'])->sum('sisa_tagihan');

        $pemasukanBulanIni = ArusKas::whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->whereIn('jenis_transaksi', ['masuk', 'mutasi_masuk'])
            ->sum('nominal');

        $pengeluaranBulanIni = ArusKas::whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->whereIn('jenis_transaksi', ['keluar', 'mutasi_keluar'])
            ->sum('nominal');

        $akunKasList = AkunKas::where('is_active', true)->get();

        return view('ketua.dashboard', compact(
            'totalPelanggan',
            'tagihanBelumLunas',
            'totalSaldo',
            'totalPiutang',
            'pemasukanBulanIni',
            'pengeluaranBulanIni',
            'akunKasList',
            'bulan',
            'tahun'
        ));
    }
}
