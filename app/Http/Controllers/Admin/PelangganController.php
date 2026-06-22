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
    public function index(Request $request)
    {
        $search = $request->search ?? '';
        $sort = $request->sort ?? 'created_at';
        $direction = $request->direction ?? 'desc';

        $query = Pelanggan::with(['user', 'pencatatanMeter' => function ($query) {
            $query->latest('tahun_periode')->latest('bulan_periode');
        }])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('pelanggan.kode_pelanggan', 'like', "%$search%")
                        ->orWhereHas('user', function ($u) use ($search) {
                            $u->where('nama_lengkap', 'like', "%$search%");
                        });
                });
            });

        if ($sort === 'nama_pelanggan') {
            $query->select('pelanggan.*')
                  ->join('users', 'pelanggan.user_id', '=', 'users.id')
                  ->orderBy('users.nama_lengkap', $direction);
        } elseif ($sort === 'kode_pelanggan') {
            $query->orderBy('pelanggan.kode_pelanggan', $direction);
        } else {
            $query->latest('pelanggan.created_at');
        }

        $pelanggan = $query->paginate(10)
            ->withQueryString();

        return view('admin.pelanggan.index', compact('pelanggan', 'search', 'sort', 'direction'));
    }

    public function create()
    {
        return view('admin.pelanggan.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_pelanggan' => 'required|string|max:255|unique:users,nama_lengkap',
            'nik'            => 'nullable|string|max:16',
            'alamat'         => 'nullable|string',
            'no_hp'          => 'nullable|string|max:15',
            'meteran_awal'   => 'required|numeric|min:0',
        ], [
            'nama_pelanggan.required' => 'Nama Pelanggan tidak boleh kosong',
            'nama_pelanggan.unique'   => 'Nama Pelanggan tidak boleh sama',
        ]);

        DB::transaction(function () use ($request) {
            $lastPelanggan = Pelanggan::latest('id')->first();
            $newNumber = $lastPelanggan ? (intval(substr($lastPelanggan->kode_pelanggan, 4)) + 1) : 1;
            $kodePelanggan = 'PLG-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);

            $user = User::create([
                'nama_lengkap' => $request->nama_pelanggan,
                'username'  => $kodePelanggan,
                'password'  => Hash::make('pelanggan123'),
                'no_hp'     => $request->no_hp,
                'alamat'    => $request->alamat,
                'role'      => 'pelanggan',
                'is_active' => true,
            ]);

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
        $request->validate([
            'nama_pelanggan' => 'required|string|max:255|unique:users,nama_lengkap,' . $pelanggan->user_id,
            'nik'            => 'nullable|string|max:16',
            'alamat'         => 'nullable|string',
            'no_hp'          => 'nullable|string|max:15',
            'meteran_awal'   => 'required|numeric|min:0',
        ], [
            'nama_pelanggan.required' => 'Nama Pelanggan tidak boleh kosong',
            'nama_pelanggan.unique'   => 'Nama Pelanggan tidak boleh sama',
        ]);

        DB::transaction(function () use ($request, $pelanggan) {
            if ($pelanggan->user) {
                $pelanggan->user->update([
                    'nama_lengkap' => $request->nama_pelanggan,
                    'no_hp'        => $request->no_hp,
                    'alamat'       => $request->alamat,
                ]);
            }

            $pelanggan->update([
                'nik'          => $request->nik,
                'meteran_awal' => $request->meteran_awal,
            ]);
        });

        return redirect()->route('admin.pelanggan.index')
            ->with('success', 'Data pelanggan berhasil diupdate!');
    }

    public function destroy(Pelanggan $pelanggan)
    {
        $pelanggan->user()->delete();
        $pelanggan->delete();

        return redirect()->route('admin.pelanggan.index')
            ->with('success', 'Pelanggan berhasil dihapus!');
    }
}
