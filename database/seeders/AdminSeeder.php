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
            'username'  => 'admin',
            'email'     => 'admin@kpspams.com',
            'password'  => Hash::make('admin123'),
            'role'      => 'admin',
            'is_active' => true,
        ]);

        Pengurus::create([
            'user_id'       => $admin->id,
            'nama_pengurus' => 'Administrator',
            'jabatan'       => 'admin',
            'no_hp'         => '08123456789',
        ]);

        // Create Ketua account
        $ketua = User::create([
            'username'  => 'ketua',
            'email'     => 'ketua@kpspams.com',
            'password'  => Hash::make('ketua123'),
            'role'      => 'ketua',
            'is_active' => true,
        ]);

        Pengurus::create([
            'user_id'       => $ketua->id,
            'nama_pengurus' => 'Ketua KP-SPAMS',
            'jabatan'       => 'ketua',
            'no_hp'         => '08198765432',
        ]);
    }
}