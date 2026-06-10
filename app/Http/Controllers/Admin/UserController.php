<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search ?? '';
        $role = $request->role ?? '';

        $users = User::with(['pelanggan', 'pengurus'])
            ->when($search, function ($q) use ($search) {
                // Gunakan function ($subQuery) agar logika OR tidak bertabrakan dengan filter Role
                $q->where(function ($subQuery) use ($search) {
                    $subQuery->where('username', 'like', "%$search%")
                        ->orWhere('nama_lengkap', 'like', "%$search%");
                });
            })
            ->when($role, fn($q) => $q->where('role', $role))
            ->latest()
            ->paginate(10);

        return view('admin.user.index', compact('users', 'search', 'role'));
    }

    public function create()
    {
        return view('admin.user.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'username'      => 'required|string|max:255|unique:users,username',
            'password'      => 'required|string|min:6|confirmed',
            'role'          => 'required|in:admin,ketua',
            'nama_pengurus' => 'required|string|max:255',
            'jabatan'       => 'nullable|string|max:255',
            'no_hp'         => 'nullable|string|max:20',
            'alamat'        => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'nama_lengkap' => $request->nama_pengurus,
                'username'     => $request->username,
                'password'     => Hash::make($request->password),
                'role'         => $request->role,
                'no_hp'        => $request->no_hp,
                'alamat'       => $request->alamat,
                'is_active'    => true,
            ]);

            $user->pengurus()->create([
                'jabatan' => $request->jabatan,
            ]);
        });

        return redirect()->route('admin.user.index')
            ->with('success', 'User pengurus berhasil ditambahkan!');
    }

    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'password_baru' => 'required|string|min:6|confirmed',
        ]);

        $user->update([
            'password' => Hash::make($request->password_baru),
        ]);

        return redirect()->route('admin.user.index')
            ->with('success', 'Password user ' . $user->username . ' berhasil direset!');
    }

    public function toggleStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat mengubah status akun sendiri!');
        }

        // Simpan status baru (true/false) ke variabel untuk digunakan di 2 tabel
        $newStatus = !$user->is_active;

        DB::transaction(function () use ($user, $newStatus) {
            // 1. Ubah status di tabel users
            $user->update([
                'is_active' => $newStatus,
            ]);

            // 2. JIKA role-nya pelanggan, update juga status di tabel pelanggan
            if ($user->role === 'pelanggan') {
                $pelanggan = Pelanggan::where('user_id', $user->id)->first();
                if ($pelanggan) {
                    // Cek tipe data kolom status Anda di tabel pelanggan (aktif/nonaktif atau 1/0)
                    // Asumsi: menggunakan ENUM ('aktif', 'nonaktif')
                    $pelanggan->update([
                        'status' => $newStatus ? 'aktif' : 'nonaktif'
                    ]);
                }
            }

            // Hapus semua session user tersebut agar otomatis logout jika dinonaktifkan
            DB::table('sessions')
                ->where('user_id', $user->id)
                ->delete();
        });

        $statusMsg = $newStatus ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', 'Akun ' . $user->username . ' berhasil ' . $statusMsg . '!');
    }

    public function show(User $user)
    {
        $user->load(['pelanggan', 'pengurus']);
        return view('admin.user.show', compact('user'));
    }

    public function edit(\App\Models\User $user)
    {
        if (!in_array($user->role, ['admin', 'ketua'])) {
            return redirect()->route('admin.user.index')->with('error', 'Hanya role Admin dan Ketua yang dapat diedit melalui form ini.');
        }

        $user->load('pengurus');
        return view('admin.user.edit', compact('user'));
    }

    public function update(Request $request, \App\Models\User $user)
    {
        $request->validate([
            'username'      => 'required|string|max:255|unique:users,username,' . $user->id,
            'role'          => 'required|in:admin,ketua',
            'nama_pengurus' => 'required|string|max:255',
            'jabatan'       => 'nullable|string|max:255',
            'no_hp'         => 'nullable|string|max:20',
            'alamat'        => 'nullable|string',
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $user) {
            // 1. Update Akun Login & Identitas di tabel User
            $user->update([
                'nama_lengkap' => $request->nama_pengurus, // Mapping dari nama_pengurus
                'username'     => $request->username,
                'role'         => $request->role,
                'no_hp'        => $request->no_hp,
                'alamat'       => $request->alamat,
            ]);

            // 2. Update Jabatan di tabel Pengurus
            $dataPengurus = [
                'jabatan' => $request->jabatan,
            ];

            if ($user->pengurus) {
                $user->pengurus->update($dataPengurus);
            } else {
                $user->pengurus()->create($dataPengurus);
            }
        });

        return redirect()->route('admin.user.show', $user->id)
            ->with('success', 'Data pengurus berhasil diperbarui!');
    }
}
