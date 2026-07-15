<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Models\AkunKas;
use App\Models\ArusKas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepositController extends Controller
{
    public function index()
    {
        $pelanggan = Pelanggan::with('user')
            ->where('status', 'aktif')
            ->get()
            ->sortBy('nama_pelanggan', SORT_NATURAL | SORT_FLAG_CASE);

        $pelangganPaginated = Pelanggan::with('user')
            ->where('status', 'aktif')
            ->paginate(8);

        $akunKas = AkunKas::where('is_active', true)->get();

        return view('admin.deposit.index', compact('pelanggan', 'pelangganPaginated', 'akunKas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'pelanggan_id' => 'required|exists:pelanggan,id',
            'akun_kas_id'  => 'required|exists:akun_kas,id',
            'nominal'      => 'required|numeric|min:1000',
            'keterangan'   => 'nullable|string|max:255',
            'foto_bukti'   => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ], [
            'pelanggan_id.required' => 'Pelanggan belum dipilih.',
            'nominal.required'      => 'Nominal tidak boleh kosong.',
            'nominal.min'           => 'Nominal minimal Rp 1.000.',
            'foto_bukti.image'      => 'Berkas bukti harus berupa gambar.',
            'foto_bukti.mimes'      => 'Format gambar bukti harus jpeg, png, atau jpg.',
            'foto_bukti.max'        => 'Ukuran gambar bukti maksimal 5MB.',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto_bukti')) {
            $fotoPath = $request->file('foto_bukti')->store('bukti/arus-kas', 'public');
        }

        DB::transaction(function () use ($request, $fotoPath) {
            $pelanggan = Pelanggan::lockForUpdate()->find($request->pelanggan_id);

            $pelanggan->increment('saldo', $request->nominal);

            $noBukti = $this->generateNoBukti('masuk', date('Y-m-d'));
            ArusKas::create([
                'akun_kas_id'     => $request->akun_kas_id,
                'jenis_transaksi' => 'masuk',
                'nominal'         => $request->nominal,
                'keterangan'      => "Deposit/Titipan Saldo: {$pelanggan->nama_pelanggan}. " . ($request->keterangan ?? ''),
                'no_bukti'        => $noBukti,
                'foto_bukti'      => $fotoPath,
                'tanggal'         => now(),
                'dicatat_oleh'    => auth()->id(),
            ]);

            AkunKas::find($request->akun_kas_id)->increment('saldo', $request->nominal);
        });

        return back()->with('success', 'Berhasil menambahkan saldo pelanggan!');
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
}
