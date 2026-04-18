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
        $startDate = $request->start_date ?: Carbon::now()->startOfMonth()->toDateString();
        $endDate = $request->end_date ?: Carbon::now()->endOfMonth()->toDateString();
        $akunKasId = $request->akun_kas_id ?? '';

        $arusKas = ArusKas::with(['akunKas'])
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->when($akunKasId, fn($q) => $q->where('akun_kas_id', $akunKasId))
            ->orderBy('tanggal', 'asc')
            ->paginate(15);

        $akunKasList = AkunKas::where('is_active', true)->get();

        $totalMasuk = ArusKas::whereBetween('tanggal', [$startDate, $endDate])
            ->when($akunKasId, fn($q) => $q->where('akun_kas_id', $akunKasId))
            ->whereIn('jenis_transaksi', ['masuk', 'mutasi_masuk'])
            ->sum('nominal');

        $totalKeluar = ArusKas::whereBetween('tanggal', [$startDate, $endDate])
            ->when($akunKasId, fn($q) => $q->where('akun_kas_id', $akunKasId))
            ->whereIn('jenis_transaksi', ['keluar', 'mutasi_keluar'])
            ->sum('nominal');

        return view('ketua.arus-kas.index', compact(
            'arusKas',
            'akunKasList',
            'startDate',
            'endDate',
            'akunKasId',
            'totalMasuk',
            'totalKeluar'
        ));
    }
}
