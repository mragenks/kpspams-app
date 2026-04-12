<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AkunKas;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(AdminSeeder::class);

        AkunKas::create([
            'nama_akun'   => 'Kas Tunai Bendahara',
            'jenis_akun'  => 'tunai',
            'saldo'       => 0,
            'keterangan'  => 'Kas tunai utama',
            'is_active'   => true,
        ]);

        AkunKas::create([
            'nama_akun'   => 'Rekening Bank BRI',
            'jenis_akun'  => 'bank',
            'saldo'       => 0,
            'keterangan'  => 'Rekening bank KP-SPAMS',
            'is_active'   => true,
        ]);

        $this->call(DataDummySeeder::class);
    }
}
