<?php

namespace App\Http\Controllers\Ketua;

use App\Http\Controllers\Controller;
use App\Models\ArusKas;
use App\Models\AkunKas;
use Illuminate\Http\Request;

class ArusKasController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->bulan ?? date('n');
        $tahun = $request->tahun ?? date('Y');
        $akunKasId = $request->akun_kas_id ?? '';

        $arusKas = ArusKas::with(['akunKas'])
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->when($akunKasId, fn($q) => $q->where('akun_kas_id', $akunKasId))
            ->orderBy('tanggal', 'asc')
            ->paginate(15);

        $akunKasList = AkunKas::where('is_active', true)->get();

        $totalMasuk = ArusKas::whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->when($akunKasId, fn($q) => $q->where('akun_kas_id', $akunKasId))
            ->whereIn('jenis_transaksi', ['masuk', 'mutasi_masuk'])
            ->sum('nominal');

        $totalKeluar = ArusKas::whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->when($akunKasId, fn($q) => $q->where('akun_kas_id', $akunKasId))
            ->whereIn('jenis_transaksi', ['keluar', 'mutasi_keluar'])
            ->sum('nominal');

        return view('ketua.arus-kas.index', compact(
            'arusKas',
            'akunKasList',
            'bulan',
            'tahun',
            'akunKasId',
            'totalMasuk',
            'totalKeluar'
        ));
    }
}
