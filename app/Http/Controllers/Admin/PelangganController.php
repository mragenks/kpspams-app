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
        $pelanggan = Pelanggan::with(['pencatatanMeter' => function ($query) {
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
            $kodePelanggan = 'PLG-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);

            // 1. Buat akun user untuk pelanggan (KOLOM NAMA, HP, ALAMAT MASUK KE SINI)
            $user = User::create([
                'nama_lengkap' => $request->nama_pelanggan, // Mapping dari input view
                'username'  => $kodePelanggan,
                'password'  => Hash::make('pelanggan123'),
                'no_hp'     => $request->no_hp,
                'alamat'    => $request->alamat,
                'role'      => 'pelanggan',
                'is_active' => true,
            ]);

            // 2. Buat data pelanggan (HANYA FOKUS PADA DATA FISIK TAGIHAN)
            Pelanggan::create([
                'user_id'        => $user->id,
                'kode_pelanggan' => $kodePelanggan,
                'nik'            => $request->nik,
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
        // 1. Tambahkan meteran_awal ke dalam validasi
        $request->validate([
            'nama_pelanggan' => 'required|string|max:255',
            'nik'            => 'nullable|string|max:16',
            'alamat'         => 'nullable|string',
            'no_hp'          => 'nullable|string|max:15',
            'status'         => 'required|in:aktif,nonaktif',
            'meteran_awal'   => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $pelanggan) {
            // Update data identitas di tabel User
            if ($pelanggan->user) {
                $pelanggan->user->update([
                    'nama_lengkap' => $request->nama_pelanggan,
                    'no_hp'        => $request->no_hp,
                    'alamat'       => $request->alamat,
                ]);
            }

            // Update data tagihan & fisik di tabel Pelanggan
            $pelanggan->update([
                'nik'          => $request->nik,
                'status'       => $request->status,
                'meteran_awal' => $request->meteran_awal,
            ]);
        });

        return redirect()->route('admin.pelanggan.index')
            ->with('success', 'Data pelanggan berhasil diupdate!');
    }

    public function destroy(Pelanggan $pelanggan)
    {
        // Hapus user terkait sekaligus (data pelanggan akan ikut terhapus karena on delete cascade)
        $pelanggan->user()->delete();
        $pelanggan->delete();

        return redirect()->route('admin.pelanggan.index')
            ->with('success', 'Pelanggan berhasil dihapus!');
    }
}
