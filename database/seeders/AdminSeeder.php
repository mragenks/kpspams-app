<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Pengurus;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin account
        $admin = User::create([
            'nama_lengkap' => 'Administrator',
            'username'     => 'admin',
            'email'        => 'admin@kpspams.com',
            'password'     => Hash::make('admin123'),
            'no_hp'        => '',
            'role'         => 'admin',
            'is_active'    => true,
        ]);

        Pengurus::create([
            'user_id'      => $admin->id,
            'jabatan'      => 'admin',
        ]);

        // Create Ketua account
        $ketua = User::create([
            'nama_lengkap' => 'Ketua KP-SPAMS',
            'username'     => 'ketua',
            'email'        => 'ketua@kpspams.com',
            'password'     => Hash::make('ketua123'),
            'no_hp'        => '',
            'role'         => 'ketua',
            'is_active'    => true,
        ]);

        Pengurus::create([
            'user_id'      => $ketua->id,
            'jabatan'      => 'ketua',
        ]);
    }
}
