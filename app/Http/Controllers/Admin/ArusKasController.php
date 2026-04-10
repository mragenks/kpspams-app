<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArusKas;
use App\Models\AkunKas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArusKasController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->bulan ?? date('n');
        $tahun = $request->tahun ?? date('Y');
        $akunKasId = $request->akun_kas_id ?? '';

        $arusKas = ArusKas::with(['akunKas', 'dicatatOleh'])
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->when($akunKasId, fn($q) => $q->where('akun_kas_id', $akunKasId))
            ->orderBy('tanggal', 'asc')
            ->orderBy('id', 'asc')
            ->paginate(15);

        $akunKasList = AkunKas::where('is_active', true)->get();

        // Summary
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

        return view('admin.arus-kas.index', compact(
            'arusKas',
            'akunKasList',
            'bulan',
            'tahun',
            'akunKasId',
            'totalMasuk',
            'totalKeluar'
        ));
    }

    public function create(Request $request)
    {
        $akunKasList = AkunKas::where('is_active', true)->get();
        $jenis = $request->jenis ?? 'masuk';
        return view('admin.arus-kas.create', compact('akunKasList', 'jenis'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'jenis_transaksi' => 'required|in:masuk,keluar',
            'akun_kas_id'     => 'required|exists:akun_kas,id',
            'nominal'         => 'required|numeric|min:1',
            'keterangan'      => 'required|string|max:255',
            'no_bukti'        => 'nullable|string|max:100',
            'tanggal'         => 'required|date',
        ]);

        $akunKas = AkunKas::findOrFail($request->akun_kas_id);

        // Validasi saldo cukup untuk pengeluaran
        if ($request->jenis_transaksi === 'keluar' && $akunKas->saldo < $request->nominal) {
            return back()->with('error', 'Saldo akun kas tidak mencukupi! Saldo saat ini: Rp ' .
                number_format($akunKas->saldo, 0, ',', '.'));
        }

        DB::transaction(function () use ($request, $akunKas) {
            ArusKas::create([
                'akun_kas_id'     => $request->akun_kas_id,
                'jenis_transaksi' => $request->jenis_transaksi,
                'nominal'         => $request->nominal,
                'keterangan'      => $request->keterangan,
                'no_bukti'        => $request->no_bukti,
                'tanggal'         => $request->tanggal,
                'dicatat_oleh'    => auth()->id(),
            ]);

            // Update saldo
            if ($request->jenis_transaksi === 'masuk') {
                $akunKas->increment('saldo', $request->nominal);
            } else {
                $akunKas->decrement('saldo', $request->nominal);
            }
        });

        return redirect()->route('admin.arus-kas.index')
            ->with('success', 'Transaksi arus kas berhasil dicatat!');
    }

    public function mutasi(Request $request)
    {
        $akunKasList = AkunKas::where('is_active', true)->get();
        return view('admin.arus-kas.mutasi', compact('akunKasList'));
    }

    public function storeMutasi(Request $request)
    {
        $request->validate([
            'akun_asal_id'   => 'required|exists:akun_kas,id',
            'akun_tujuan_id' => 'required|exists:akun_kas,id|different:akun_asal_id',
            'nominal'        => 'required|numeric|min:1',
            'keterangan'     => 'required|string|max:255',
            'tanggal'        => 'required|date',
        ]);

        $akunAsal = AkunKas::findOrFail($request->akun_asal_id);
        $akunTujuan = AkunKas::findOrFail($request->akun_tujuan_id);

        if ($akunAsal->saldo < $request->nominal) {
            return back()->with('error', 'Saldo akun asal tidak mencukupi! Saldo: Rp ' .
                number_format($akunAsal->saldo, 0, ',', '.'));
        }

        DB::transaction(function () use ($request, $akunAsal, $akunTujuan) {
            // Catat mutasi keluar
            ArusKas::create([
                'akun_kas_id'     => $request->akun_asal_id,
                'jenis_transaksi' => 'mutasi_keluar',
                'nominal'         => $request->nominal,
                'keterangan'      => 'Mutasi ke ' . $akunTujuan->nama_akun . ' - ' . $request->keterangan,
                'tanggal'         => $request->tanggal,
                'dicatat_oleh'    => auth()->id(),
            ]);

            // Catat mutasi masuk
            ArusKas::create([
                'akun_kas_id'     => $request->akun_tujuan_id,
                'jenis_transaksi' => 'mutasi_masuk',
                'nominal'         => $request->nominal,
                'keterangan'      => 'Mutasi dari ' . $akunAsal->nama_akun . ' - ' . $request->keterangan,
                'tanggal'         => $request->tanggal,
                'dicatat_oleh'    => auth()->id(),
            ]);

            // Update saldo
            $akunAsal->decrement('saldo', $request->nominal);
            $akunTujuan->increment('saldo', $request->nominal);
        });

        return redirect()->route('admin.arus-kas.index')
            ->with('success', 'Mutasi dana berhasil dicatat!');
    }

    public function destroy(ArusKas $arusKas)
    {
        // Kembalikan saldo
        $akunKas = $arusKas->akunKas;

        if (in_array($arusKas->jenis_transaksi, ['masuk', 'mutasi_masuk'])) {
            $akunKas->decrement('saldo', $arusKas->nominal);
        } else {
            $akunKas->increment('saldo', $arusKas->nominal);
        }

        $arusKas->delete();

        return redirect()->route('admin.arus-kas.index')
            ->with('success', 'Transaksi berhasil dihapus dan saldo dikembalikan!');
    }
}
