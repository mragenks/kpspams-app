<?php

namespace App\Http\Controllers\Admin;

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

        $tagihanBelumLunas = TagihanAir::whereIn('status_bayar', ['belum_lunas', 'mencicil'])
            ->count();

        $totalSaldo = AkunKas::where('is_active', true)->sum('saldo');

        $totalAset = Inventaris::count();

        $tagihanBulanIni = TagihanAir::where('bulan_periode', $bulan)
            ->where('tahun_periode', $tahun)
            ->get();

        $totalTagihanBulanIni = $tagihanBulanIni->sum('total_tagihan');
        $totalTerbayarBulanIni = $tagihanBulanIni->sum('total_terbayar');

        $pemasukanBulanIni = ArusKas::whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->whereIn('jenis_transaksi', ['masuk', 'mutasi_masuk'])
            ->sum('nominal');

        $pengeluaranBulanIni = ArusKas::whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->whereIn('jenis_transaksi', ['keluar', 'mutasi_keluar'])
            ->sum('nominal');

        $totalPiutang = TagihanAir::whereIn('status_bayar', ['belum_lunas', 'mencicil'])
            ->sum('sisa_tagihan');

        $tagihanTerbaru = TagihanAir::with('pelanggan')
            ->whereIn('status_bayar', ['belum_lunas', 'mencicil'])
            ->where('bulan_periode', $bulan)
            ->where('tahun_periode', $tahun)
            ->latest()
            ->take(5)
            ->get();

        $transaksiTerbaru = ArusKas::with('akunKas')
            ->latest()
            ->take(5)
            ->get();

        $asetRusak = Inventaris::where('kondisi', 'rusak')
            ->orWhere('kondisi', 'perlu_perbaikan')
            ->count();

        $chartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $m = $date->month;
            $y = $date->year;

            $masuk = ArusKas::whereMonth('tanggal', $m)
                ->whereYear('tanggal', $y)
                ->whereIn('jenis_transaksi', ['masuk', 'mutasi_masuk'])
                ->sum('nominal');

            $keluar = ArusKas::whereMonth('tanggal', $m)
                ->whereYear('tanggal', $y)
                ->whereIn('jenis_transaksi', ['keluar', 'mutasi_keluar'])
                ->sum('nominal');

            $chartData[] = [
                'bulan' => $date->isoFormat('MMM Y'),
                'masuk' => $masuk,
                'keluar' => $keluar,
            ];
        }

        $buktiPending = \App\Models\Pembayaran::where('status_validasi', 'pending')
            ->where('nominal_bayar', 0)
            ->with(['pelanggan', 'tagihan'])
            ->latest()
            ->get();

        return view('admin.dashboard', compact(
            'totalPelanggan',
            'tagihanBelumLunas',
            'totalSaldo',
            'totalAset',
            'totalTagihanBulanIni',
            'totalTerbayarBulanIni',
            'pemasukanBulanIni',
            'pengeluaranBulanIni',
            'totalPiutang',
            'tagihanTerbaru',
            'transaksiTerbaru',
            'asetRusak',
            'chartData',
            'bulan',
            'tahun',
            'buktiPending' 
        ));
    }
}
