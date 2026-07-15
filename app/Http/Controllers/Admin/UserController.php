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
        $sort = $request->sort ?? 'created_at';
        $direction = $request->direction ?? 'desc';

        $query = User::with(['pelanggan', 'pengurus'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($subQuery) use ($search) {
                    $subQuery->where('username', 'like', "%$search%")
                        ->orWhere('nama_lengkap', 'like', "%$search%");
                });
            })
            ->when($role, fn($q) => $q->where('role', $role));

        if ($sort === 'username') {
            $query->orderBy('username', $direction);
        } elseif ($sort === 'nama_lengkap') {
            $query->orderBy('nama_lengkap', $direction);
        } else {
            $query->latest('created_at');
        }

        $users = $query->paginate(10)->withQueryString();

        return view('admin.user.index', compact('users', 'search', 'role', 'sort', 'direction'));
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
            'no_hp'         => 'nullable|numeric|digits_between:10,15',
            'alamat'        => 'nullable|string',
        ], [
            'username.required'      => 'Username wajib diisi!',
            'username.unique'        => 'Username sudah terdaftar, silakan gunakan yang lain!',
            'password.required'      => 'Password wajib diisi!',
            'password.min'           => 'Password minimal harus 6 karakter!',
            'password.confirmed'     => 'Konfirmasi password tidak sesuai!',
            'role.required'          => 'Role akses wajib dipilih!',
            'role.in'                => 'Pilihan role tidak valid!',
            'nama_pengurus.required' => 'Nama lengkap pengurus wajib diisi!',
            'no_hp.numeric'          => 'Peringatan: Nomor HP hanya boleh diisi dengan angka!',
            'no_hp.digits_between'   => 'Nomor HP harus antara 10 hingga 15 digit angka!',
        ]);

        if ($request->role === 'ketua') {
            $activeKetuaExists = User::where('role', 'ketua')->where('is_active', true)->exists();
            if ($activeKetuaExists) {
                return back()->withInput()->withErrors([
                    'role' => 'Hanya boleh ada 1 Ketua yang aktif pada saat yang sama. Silakan nonaktifkan Ketua yang lama terlebih dahulu!'
                ]);
            }
        }

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
                'jabatan' => $request->jabatan ?? '-',
            ]);
        });

        return redirect()->route('admin.user.index')
            ->with('success', 'User pengurus berhasil ditambahkan!');
    }

    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'password_baru' => 'required|string|min:6|confirmed',
        ], [
            'password_baru.required'  => 'Password baru wajib diisi!',
            'password_baru.min'       => 'Password baru minimal harus 6 karakter!',
            'password_baru.confirmed' => 'Konfirmasi password baru tidak sesuai!',
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

        $newStatus = !$user->is_active;

        if ($user->role === 'ketua' && $newStatus) {
            $activeKetuaExists = User::where('role', 'ketua')
                ->where('is_active', true)
                ->where('id', '!=', $user->id)
                ->exists();
            if ($activeKetuaExists) {
                return back()->with('error', 'Gagal mengaktifkan! Hanya boleh ada 1 Ketua yang aktif pada saat yang sama. Silakan nonaktifkan Ketua yang lama terlebih dahulu!');
            }
        }

        DB::transaction(function () use ($user, $newStatus) {
            $user->update([
                'is_active' => $newStatus,
            ]);

            if ($user->role === 'pelanggan') {
                $pelanggan = Pelanggan::where('user_id', $user->id)->first();
                if ($pelanggan) {
                    $pelanggan->update([
                        'status' => $newStatus ? 'aktif' : 'nonaktif'
                    ]);
                }
            }

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
            'no_hp'         => 'nullable|numeric|digits_between:10,15',
            'alamat'        => 'nullable|string',
        ], [
            'username.required'      => 'Username wajib diisi!',
            'username.unique'        => 'Username sudah terdaftar, silakan gunakan yang lain!',
            'role.required'          => 'Role akses wajib dipilih!',
            'role.in'                => 'Pilihan role tidak valid!',
            'nama_pengurus.required' => 'Nama lengkap pengurus wajib diisi!',
            'no_hp.numeric'          => 'Peringatan: Nomor HP hanya boleh diisi dengan angka!',
            'no_hp.digits_between'   => 'Nomor HP harus antara 10 hingga 15 digit angka!',
        ]);

        if ($request->role === 'ketua' && $user->is_active) {
            $activeKetuaExists = User::where('role', 'ketua')
                ->where('is_active', true)
                ->where('id', '!=', $user->id)
                ->exists();
            if ($activeKetuaExists) {
                return back()->withInput()->withErrors([
                    'role' => 'Hanya boleh ada 1 Ketua yang aktif pada saat yang sama. Silakan nonaktifkan Ketua yang lama terlebih dahulu!'
                ]);
            }
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $user) {
            $user->update([
                'nama_lengkap' => $request->nama_pengurus,
                'username'     => $request->username,
                'role'         => $request->role,
                'no_hp'        => $request->no_hp,
                'alamat'       => $request->alamat,
            ]);

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
