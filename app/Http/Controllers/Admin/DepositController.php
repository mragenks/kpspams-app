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
        ], [
            'pelanggan_id.required' => 'Pelanggan belum dipilih.',
            'nominal.required'      => 'Nominal tidak boleh kosong.',
            'nominal.min'           => 'Nominal minimal Rp 1.000.',
        ]);

        DB::transaction(function () use ($request) {
            $pelanggan = Pelanggan::lockForUpdate()->find($request->pelanggan_id);

            $pelanggan->increment('saldo', $request->nominal);

            ArusKas::create([
                'akun_kas_id'     => $request->akun_kas_id,
                'jenis_transaksi' => 'masuk',
                'nominal'         => $request->nominal,
                'keterangan'      => "Deposit/Titipan Saldo: {$pelanggan->nama_pelanggan}. " . ($request->keterangan ?? ''),
                'tanggal'         => now(),
                'dicatat_oleh'    => auth()->id(),
            ]);

            AkunKas::find($request->akun_kas_id)->increment('saldo', $request->nominal);
        });

        return back()->with('success', 'Berhasil menambahkan saldo pelanggan!');
    }
}
