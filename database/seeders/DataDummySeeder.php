<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pelanggan;
use App\Models\AkunKas;
use App\Models\PencatatanMeter;
use App\Models\TagihanAir;
use App\Models\ArusKas;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DataDummySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ambil Admin (Pencatat)
        $admin = User::where('role', 'admin')->first();

        if (!$admin) {
            $this->command->error('Admin tidak ditemukan! Jalankan AdminSeeder terlebih dahulu.');
            return;
        }

        // 2. Siapkan Akun Kas
        $kasBendahara = AkunKas::firstOrCreate(
            ['nama_akun' => 'Kas Tunai Bendahara'],
            ['saldo' => 0, 'is_active' => true]
        );

        // 3. Nama Pelanggan
        $names = [
            'Ahmad Syarif',
            'Budi Santoso',
            'Citra Lestari',
            'Dedi Wijaya',
            'Eka Putri',
            'Fahmi Idris',
            'Gita Permata',
            'Heri Setiawan',
            'Indah Sari',
            'Joko Susilo'
        ];

        foreach ($names as $i => $name) {
            // Tentukan Kode Pelanggan duluan
            $kodePelanggan = 'PLG-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT);

            // 4. Buat Akun User (Username = Kode Pelanggan)
            $userPelanggan = User::create([
                'username'  => $kodePelanggan, // Menggunakan Kode Pelanggan untuk Login
                'email'     => Str::slug($name) . "@kpspams.com",
                'password'  => Hash::make('pelanggan123'),
                'role'      => 'pelanggan',
                'is_active' => true,
            ]);

            // 5. Buat Data Pelanggan
            $pelanggan = Pelanggan::create([
                'user_id'        => $userPelanggan->id,
                'kode_pelanggan' => $kodePelanggan,
                'nama_pelanggan' => $name,
                'alamat'         => 'Desa Singodutan RT 0' . rand(1, 5),
                'no_hp'          => '0812' . rand(10000000, 99999999),
                'meteran_awal'   => rand(10, 50),
                'status'         => 'aktif',
                'saldo'          => 0,
                'created_at'     => Carbon::now()->subMonths(4)
            ]);

            // 6. Buat Riwayat 3 Bulan (Jan - Mar 2026)
            $meterSekarang = $pelanggan->meteran_awal;

            for ($m = 1; $m <= 3; $m++) {
                $bulan = $m;
                $tahun = 2026;
                $meterLalu = $meterSekarang;
                $pemakaian = rand(12, 30);
                $meterSekarang += $pemakaian;

                $biayaBeban = 5000;
                $biayaPakai = 10000 + (max(0, $pemakaian - 10) * 2000);
                $total = $biayaPakai + $biayaBeban;

                $pencatatan = PencatatanMeter::create([
                    'pelanggan_id'       => $pelanggan->id,
                    'bulan_periode'      => $bulan,
                    'tahun_periode'      => $tahun,
                    'meter_awal'         => $meterLalu,
                    'meter_akhir'        => $meterSekarang,
                    'tanggal_pencatatan' => Carbon::create($tahun, $bulan, rand(1, 5)),
                    'dicatat_oleh'       => $admin->id
                ]);

                TagihanAir::create([
                    'pelanggan_id'        => $pelanggan->id,
                    'pencatatan_meter_id' => $pencatatan->id,
                    'bulan_periode'       => $bulan,
                    'tahun_periode'       => $tahun,
                    'biaya_beban'         => $biayaBeban,
                    'biaya_pemakaian'     => $biayaPakai,
                    'total_tagihan'       => $total,
                    'total_terbayar'      => $total,
                    'sisa_tagihan'        => 0,
                    'status_bayar'        => 'lunas'
                ]);

                ArusKas::create([
                    'akun_kas_id'     => $kasBendahara->id,
                    'jenis_transaksi' => 'masuk',
                    'nominal'         => $total,
                    'keterangan'      => "Pelunasan Tagihan: {$name} ({$kodePelanggan}) - Periode {$bulan}",
                    'tanggal'         => Carbon::create($tahun, $bulan, rand(10, 25)),
                    'dicatat_oleh'    => $admin->id
                ]);

                $kasBendahara->increment('saldo', $total);
            }
        }
    }
}
