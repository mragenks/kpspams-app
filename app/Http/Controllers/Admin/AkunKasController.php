<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AkunKas;
use Illuminate\Http\Request;

class AkunKasController extends Controller
{
    public function index()
    {
        $akunKas = AkunKas::latest()->get();
        return view('admin.akun-kas.index', compact('akunKas'));
    }

    public function create()
    {
        return view('admin.akun-kas.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_akun'   => 'required|string|max:255|unique:akun_kas,nama_akun',
            'jenis_akun'  => 'required|in:tunai,bank',
            'saldo'       => 'required|numeric|min:0',
            'keterangan'  => 'nullable|string',
        ], [
            'nama_akun.required' => 'Nama Akun Kas tidak boleh kosong.',
            'nama_akun.unique'   => 'Nama Akun Kas sudah ada, silakan gunakan nama yang lain.',
        ]);

        AkunKas::create([
            'nama_akun'  => $request->nama_akun,
            'jenis_akun' => $request->jenis_akun,
            'saldo'      => $request->saldo,
            'keterangan' => $request->keterangan,
            'is_active'  => true,
        ]);

        return redirect()->route('admin.akun-kas.index')
            ->with('success', 'Akun kas berhasil ditambahkan!');
    }

    public function edit(AkunKas $akunKas)
    {
        return view('admin.akun-kas.edit', compact('akunKas'));
    }

    public function update(Request $request, AkunKas $akunKas)
    {
        $request->validate([
            'nama_akun'  => 'required|string|max:255|unique:akun_kas,nama_akun,' . $akunKas->id,
            'jenis_akun' => 'required|in:tunai,bank',
            'keterangan' => 'nullable|string',
            'is_active'  => 'boolean',
        ], [
            'nama_akun.required' => 'Nama Akun Kas tidak boleh kosong.',
            'nama_akun.unique'   => 'Nama Akun Kas sudah ada, silakan gunakan nama yang lain.',
        ]);

        $akunKas->update([
            'nama_akun'  => $request->nama_akun,
            'jenis_akun' => $request->jenis_akun,
            'keterangan' => $request->keterangan,
            'is_active'  => $request->has('is_active'),
        ]);

        return redirect()->route('admin.akun-kas.index')
            ->with('success', 'Akun kas berhasil diupdate!');
    }

    public function destroy(AkunKas $akunKas)
    {
        // Cek apakah saldo masih tersisa
        // if ($akunKas->saldo > 0) {
        //     return back()->with('error', 'Gagal: Akun kas tidak dapat dihapus karena masih memiliki sisa saldo sebesar Rp ' . number_format($akunKas->saldo, 0, ',', '.') . '!');
        // }

        // Cek apakah akun punya transaksi
        if ($akunKas->arusKas()->count() > 0) {
            return back()->with('error', 'Akun kas tidak dapat dihapus karena memiliki riwayat transaksi!');
        }

        $akunKas->delete();

        return redirect()->route('admin.akun-kas.index')
            ->with('success', 'Akun kas berhasil dihapus!');
    }
}
