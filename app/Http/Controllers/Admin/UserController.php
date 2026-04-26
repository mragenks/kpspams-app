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

        $user->update([
            'is_active' => !$user->is_active,
        ]);

        // Hapus semua session user tersebut
        DB::table('sessions')
            ->where('user_id', $user->id)
            ->delete();

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', 'Akun ' . $user->username . ' berhasil ' . $status . '!');
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
