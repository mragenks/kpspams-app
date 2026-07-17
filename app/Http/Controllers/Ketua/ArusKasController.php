<?php

namespace App\Http\Controllers\Ketua;

use App\Http\Controllers\Controller;
use App\Models\ArusKas;
use App\Models\AkunKas;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ArusKasController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->bulan ?? date('n');
        $tahun = $request->tahun ?? date('Y');
        $akunKasId = $request->akun_kas_id ?? '';
        $jenis = $request->jenis ?? '';

        $arusKas = ArusKas::with(['akunKas', 'dicatatOleh'])
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->when($akunKasId, fn($q) => $q->where('akun_kas_id', $akunKasId))
            ->when($jenis, function ($q) use ($jenis) {
                if ($jenis === 'masuk') {
                    $q->whereIn('jenis_transaksi', ['masuk', 'mutasi_masuk']);
                } elseif ($jenis === 'keluar') {
                    $q->whereIn('jenis_transaksi', ['keluar', 'mutasi_keluar']);
                }
            })
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

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
            'jenis',
            'totalMasuk',
            'totalKeluar'
        ));
    }
}
