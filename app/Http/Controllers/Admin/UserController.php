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
                $q->where('username', 'like', "%$search%");
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
}
