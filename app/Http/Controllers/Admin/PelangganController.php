<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class PelangganController extends Controller
{
    public function index()
    {
        // $pelanggan = Pelanggan::with('user')->latest()->paginate(10);
        $pelanggan = Pelanggan::with(['pencatatanMeter' => function($query) {
            $query->latest('tahun_periode')->latest('bulan_periode');
        }])->latest()->paginate(10);
        return view('admin.pelanggan.index', compact('pelanggan'));
    }

    public function create()
    {
        return view('admin.pelanggan.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_pelanggan' => 'required|string|max:255',
            'nik'            => 'nullable|string|max:16',
            'alamat'         => 'nullable|string',
            'no_hp'          => 'nullable|string|max:15',
            'meteran_awal'   => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            // Generate kode pelanggan otomatis
            $lastPelanggan = Pelanggan::latest('id')->first();
            $newNumber = $lastPelanggan ? (intval(substr($lastPelanggan->kode_pelanggan, 4)) + 1) : 1;
            $kodePelanggan = 'PLG-' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);

            // Buat akun user untuk pelanggan
            $user = User::create([
                'username'  => $kodePelanggan,
                'password'  => Hash::make('pelanggan123'),
                'role'      => 'pelanggan',
                'is_active' => true,
            ]);

            // Buat data pelanggan
            Pelanggan::create([
                'user_id'        => $user->id,
                'kode_pelanggan' => $kodePelanggan,
                'nama_pelanggan' => $request->nama_pelanggan,
                'nik'            => $request->nik,
                'alamat'         => $request->alamat,
                'no_hp'          => $request->no_hp,
                'meteran_awal'   => $request->meteran_awal,
                'status'         => 'aktif',
            ]);
        });

        return redirect()->route('admin.pelanggan.index')
            ->with('success', 'Pelanggan berhasil ditambahkan!');
    }

    public function show(Pelanggan $pelanggan)
    {
        $pelanggan->load('tagihanAir', 'pencatatanMeter');
        return view('admin.pelanggan.show', compact('pelanggan'));
    }

    public function edit(Pelanggan $pelanggan)
    {
        return view('admin.pelanggan.edit', compact('pelanggan'));
    }

    public function update(Request $request, Pelanggan $pelanggan)
    {
        $request->validate([
            'nama_pelanggan' => 'required|string|max:255',
            'nik'            => 'nullable|string|max:16',
            'alamat'         => 'nullable|string',
            'no_hp'          => 'nullable|string|max:15',
            'status'         => 'required|in:aktif,nonaktif',
        ]);

        $pelanggan->update($request->only([
            'nama_pelanggan',
            'nik',
            'alamat',
            'no_hp',
            'status'
        ]));

        return redirect()->route('admin.pelanggan.index')
            ->with('success', 'Data pelanggan berhasil diupdate!');
    }

    public function destroy(Pelanggan $pelanggan)
    {
        // Hapus user terkait sekaligus
        $pelanggan->user()->delete();
        $pelanggan->delete();

        return redirect()->route('admin.pelanggan.index')
            ->with('success', 'Pelanggan berhasil dihapus!');
    }
}
