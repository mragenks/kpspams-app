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
            'nama_lengkap' => 'Administrator', // Ditambahkan ke User
            'username'     => 'admin',
            'email'        => 'admin@kpspams.com',
            'password'     => Hash::make('admin123'),
            'no_hp'        => '085225003050',   // Ditambahkan ke User
            'role'         => 'admin',
            'is_active'    => true,
        ]);

        Pengurus::create([
            'user_id'      => $admin->id,
            'jabatan'      => 'admin',
            // nama_pengurus dan no_hp dihapus dari sini
        ]);

        // Create Ketua account
        $ketua = User::create([
            'nama_lengkap' => 'Ketua KP-SPAMS', // Ditambahkan ke User
            'username'     => 'ketua',
            'email'        => 'ketua@kpspams.com',
            'password'     => Hash::make('ketua123'),
            'no_hp'        => '08198765432',    // Ditambahkan ke User
            'role'         => 'ketua',
            'is_active'    => true,
        ]);

        Pengurus::create([
            'user_id'      => $ketua->id,
            'jabatan'      => 'ketua',
            // nama_pengurus dan no_hp dihapus dari sini
        ]);
    }
}
