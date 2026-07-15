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
            ->orderBy('tanggal', 'asc')
            ->orderBy('id', 'asc')
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

        return view('admin.arus-kas.index', compact(
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
            'nominal'         => 'required|integer|min:1',
            'keterangan'      => 'required|string|max:255',
            'tanggal'         => 'required|date',
            'foto_bukti'      => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ], [
            'jenis_transaksi.required' => 'Jenis transaksi wajib dipilih.',
            'akun_kas_id.required'     => 'Akun kas wajib dipilih.',
            'nominal.required'         => 'Nominal wajib diisi.',
            'nominal.integer'          => 'Peringatan: Nominal harus berupa angka bulat (tidak boleh desimal atau menggunakan koma/titik)!',
            'nominal.min'              => 'Peringatan: Nominal tidak boleh minus, nol, atau kurang dari Rp 1!',
            'keterangan.required'      => 'Keterangan wajib diisi.',
            'tanggal.required'         => 'Tanggal wajib diisi.',
            'foto_bukti.image'         => 'File yang diunggah harus berupa gambar.',
            'foto_bukti.mimes'         => 'Format gambar harus JPG, JPEG, atau PNG.',
            'foto_bukti.max'           => 'Ukuran foto bukti maksimal adalah 5 MB.',
        ]);

        $akunKas = AkunKas::findOrFail($request->akun_kas_id);

        if ($request->jenis_transaksi === 'keluar' && $akunKas->saldo < $request->nominal) {
            return back()->with('error', 'Saldo akun kas tidak mencukupi! Saldo saat ini: Rp ' .
                number_format($akunKas->saldo, 0, ',', '.'));
        }

        $fotoPath = null;
        if ($request->hasFile('foto_bukti')) {
            $fotoPath = $request->file('foto_bukti')->store('bukti/arus-kas', 'public');
        }

        DB::transaction(function () use ($request, $akunKas, $fotoPath) {
            $noBukti = $this->generateNoBukti($request->jenis_transaksi, $request->tanggal);

            ArusKas::create([
                'akun_kas_id'     => $request->akun_kas_id,
                'jenis_transaksi' => $request->jenis_transaksi,
                'nominal'         => $request->nominal,
                'keterangan'      => $request->keterangan,
                'no_bukti'        => $noBukti,
                'foto_bukti'      => $fotoPath,
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
            'nominal'        => 'required|integer|min:1',
            'keterangan'     => 'required|string|max:255',
            'tanggal'        => 'required|date',
            'foto_bukti'     => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ], [
            'akun_asal_id.required'   => 'Akun asal wajib dipilih.',
            'akun_tujuan_id.required' => 'Akun tujuan wajib dipilih.',
            'akun_tujuan_id.different' => 'Dana Asal dan Dana Tujuan tidak boleh sama.',
            'nominal.required'        => 'Nominal mutasi wajib diisi.',
            'nominal.integer'         => 'Peringatan: Nominal mutasi harus berupa angka bulat (tidak boleh desimal atau menggunakan koma/titik)!',
            'nominal.min'             => 'Peringatan: Nominal mutasi tidak boleh minus, nol, atau kurang dari Rp 1!',
            'keterangan.required'     => 'Keterangan wajib diisi.',
            'tanggal.required'        => 'Tanggal wajib diisi.',
            'foto_bukti.image'        => 'File yang diunggah harus berupa gambar.',
            'foto_bukti.mimes'        => 'Format gambar harus JPG, JPEG, atau PNG.',
            'foto_bukti.max'          => 'Ukuran foto bukti maksimal adalah 5 MB.',
        ]);

        $akunAsal = AkunKas::findOrFail($request->akun_asal_id);
        $akunTujuan = AkunKas::findOrFail($request->akun_tujuan_id);

        if ($akunAsal->saldo < $request->nominal) {
            return back()->with('error', 'Saldo akun asal tidak mencukupi! Saldo: Rp ' .
                number_format($akunAsal->saldo, 0, ',', '.'));
        }

        $fotoPath = null;
        if ($request->hasFile('foto_bukti')) {
            $fotoPath = $request->file('foto_bukti')->store('bukti/mutasi', 'public');
        }

        DB::transaction(function () use ($request, $akunAsal, $akunTujuan, $fotoPath) {
            $noBukti = $this->generateNoBukti('mutasi', $request->tanggal);

            ArusKas::create([
                'akun_kas_id'     => $request->akun_asal_id,
                'jenis_transaksi' => 'mutasi_keluar',
                'nominal'         => $request->nominal,
                'keterangan'      => 'Mutasi ke ' . $akunTujuan->nama_akun . ' - ' . $request->keterangan,
                'no_bukti'        => $noBukti,
                'foto_bukti'      => $fotoPath,
                'tanggal'         => $request->tanggal,
                'dicatat_oleh'    => auth()->id(),
            ]);

            ArusKas::create([
                'akun_kas_id'     => $request->akun_tujuan_id,
                'jenis_transaksi' => 'mutasi_masuk',
                'nominal'         => $request->nominal,
                'keterangan'      => 'Mutasi dari ' . $akunAsal->nama_akun . ' - ' . $request->keterangan,
                'no_bukti'        => $noBukti,
                'foto_bukti'      => $fotoPath,
                'tanggal'         => $request->tanggal,
                'dicatat_oleh'    => auth()->id(),
            ]);

            $akunAsal->decrement('saldo', $request->nominal);
            $akunTujuan->increment('saldo', $request->nominal);
        });

        return redirect()->route('admin.arus-kas.index')
            ->with('success', 'Mutasi dana berhasil dicatat!');
    }

    private function generateNoBukti($jenis, $tanggal)
    {
        $dateStr = date('Ymd', strtotime($tanggal));
        
        $prefixCode = 'MUT';
        if ($jenis === 'masuk') {
            $prefixCode = 'MAS';
        } elseif ($jenis === 'keluar') {
            $prefixCode = 'KEL';
        }
        
        $prefix = $prefixCode . '-' . $dateStr . '-';

        $latest = ArusKas::where('no_bukti', 'like', $prefix . '%')
            ->orderBy('no_bukti', 'desc')
            ->first();

        if ($latest) {
            $lastNumber = intval(substr($latest->no_bukti, strlen($prefix)));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function destroy(ArusKas $arusKas)
    {
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
