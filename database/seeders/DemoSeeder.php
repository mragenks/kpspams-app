<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Pengurus;
use App\Models\Pelanggan;
use App\Models\PengaturanTarif;
use App\Models\AkunKas;
use App\Models\PencatatanMeter;
use App\Models\TagihanAir;
use App\Models\Pembayaran;
use App\Models\ArusKas;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DemoSeeder extends Seeder
{
    /**
     * Algoritma Perhitungan Tarif Air Sesuai Aturan SK Desa
     */
    private function hitungTarifAir($volume, $tarif)
    {
        $biaya = 0;

        if ($volume > 0) {
            // Blok 1: 0 - 5 m3 (Flat 10.000)
            $biaya += $tarif->tarif_0_5;

            // Blok 2: 6 - 10 m3 (Flat 10.000)
            if ($volume > 5) {
                $biaya += $tarif->tarif_6_10;
            }

            // Blok 3: 11 - 20 m3 (Per Kubik 2.200)
            if ($volume > 10) {
                $vol_blok3 = min($volume - 10, 10);
                $biaya += ($vol_blok3 * $tarif->tarif_11_20);
            }

            // Blok 4: 21 - 30 m3 (Per Kubik 2.500)
            if ($volume > 20) {
                $vol_blok4 = min($volume - 20, 10);
                $biaya += ($vol_blok4 * $tarif->tarif_21_30);
            }

            // Blok 5: > 30 m3 (Per Kubik 3.000)
            if ($volume > 30) {
                $vol_blok5 = $volume - 30;
                $biaya += ($vol_blok5 * $tarif->tarif_atas_30);
            }
        }

        return $biaya;
    }

    public function run(): void
    {
        // 1. Buat Data Akun Kas Utama
        $kasUtama = AkunKas::create([
            'nama_akun' => 'Kas Tunai Bendahara',
            'jenis_akun' => 'tunai',
            'saldo' => 0, // Akan diupdate nanti saat pembayaran
            'is_active' => true,
        ]);

        $kasBank = AkunKas::create([
            'nama_akun' => 'Rekening Bank BRI',
            'jenis_akun' => 'bank',
            'saldo' => 5000000, // Saldo modal awal
            'is_active' => true,
        ]);

        // 2. Buat Data Admin & Ketua
        $adminUser = User::create([
            'nama_lengkap' => 'Administrator Demo',
            'username' => 'admin',
            'email' => 'admin@kpspams.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);
        Pengurus::create(['user_id' => $adminUser->id, 'jabatan' => 'Admin Utama']);

        $ketuaUser = User::create([
            'nama_lengkap' => 'Bapak Ketua',
            'username' => 'ketua',
            'email' => 'ketua@kpspams.com',
            'password' => Hash::make('ketua123'),
            'role' => 'ketua',
        ]);
        Pengurus::create(['user_id' => $ketuaUser->id, 'jabatan' => 'Ketua PAMSIMAS']);

        // 3. Buat Pengaturan Tarif Berdasarkan SK
        $pengaturanTarif = PengaturanTarif::create([
            'biaya_beban' => 5000,
            'tarif_0_5' => 10000,
            'tarif_6_10' => 10000,
            'tarif_11_20' => 2200,
            'tarif_21_30' => 2500,
            'tarif_atas_30' => 3000,
        ]);

        // 4. Buat 15 Pelanggan
        $pelangganList = [];
        for ($i = 1; $i <= 15; $i++) {
            $kode = 'PLG-' . str_pad($i, 4, '0', STR_PAD_LEFT);

            $user = User::create([
                'nama_lengkap' => 'Pelanggan Warga ' . $i,
                'username' => $kode,
                'password' => Hash::make('pelanggan123'),
                'role' => 'pelanggan',
                'alamat' => 'Desa Singodutan RT 0' . rand(1, 5),
            ]);

            $pelanggan = Pelanggan::create([
                'user_id' => $user->id,
                'kode_pelanggan' => $kode,
                'status' => 'aktif',
                'meteran_awal' => rand(10, 50),
            ]);

            $pelangganList[] = $pelanggan;
        }

        // 5. Buat Siklus 6 Bulan (Januari - Juni 2026)
        $totalSaldoKas = 0;

        // Loop 6 Bulan
        for ($bulan = 1; $bulan <= 6; $bulan++) {
            $tahun = 2026;

            foreach ($pelangganList as $plg) {
                // Ambil meter awal (Bulan 1 pakai meteran_awal, bulan selanjutnya pakai meter_akhir bulan sblmnya)
                if ($bulan == 1) {
                    $meter_sebelumnya = $plg->meteran_awal;
                } else {
                    $catat_sblm = PencatatanMeter::where('pelanggan_id', $plg->id)
                        ->where('bulan_periode', $bulan - 1)
                        ->first();
                    $meter_sebelumnya = $catat_sblm->meter_akhir;
                }

                // Random pemakaian per bulan (Rata-rata 5 sampai 25 kubik)
                $volume_pakai = rand(5, 25);
                $meter_akhir_baru = $meter_sebelumnya + $volume_pakai;

                // Tanggal Pencatatan (Awal bulan)
                $tgl_catat = Carbon::createFromDate($tahun, $bulan, rand(1, 5));

                // Insert Pencatatan Meter
                $catatMeter = PencatatanMeter::create([
                    'pelanggan_id' => $plg->id,
                    'bulan_periode' => $bulan,
                    'tahun_periode' => $tahun,
                    'meter_awal' => $meter_sebelumnya,
                    'meter_akhir' => $meter_akhir_baru,
                    // volume_m3 otomatis di-generate MySQL
                    'tanggal_pencatatan' => $tgl_catat,
                    'dicatat_oleh' => $adminUser->id,
                ]);

                // Hitung Tagihan
                $biaya_pemakaian = $this->hitungTarifAir($volume_pakai, $pengaturanTarif);
                $total_tagihan = $pengaturanTarif->biaya_beban + $biaya_pemakaian;

                // Tentukan Status Bayar (Bulan 1-5 otomatis lunas, Bulan 6 "Juni" sengaja dibuat Belum Lunas untuk demo)
                $status_bayar = ($bulan < 6) ? 'lunas' : 'belum_lunas';
                $total_terbayar = ($bulan < 6) ? $total_tagihan : 0;
                $sisa_tagihan = ($bulan < 6) ? 0 : $total_tagihan;

                $tagihan = TagihanAir::create([
                    'pelanggan_id' => $plg->id,
                    'pencatatan_meter_id' => $catatMeter->id,
                    'bulan_periode' => $bulan,
                    'tahun_periode' => $tahun,
                    'biaya_beban' => $pengaturanTarif->biaya_beban,
                    'biaya_pemakaian' => $biaya_pemakaian,
                    'total_tagihan' => $total_tagihan,
                    'total_terbayar' => $total_terbayar,
                    'sisa_tagihan' => $sisa_tagihan,
                    'status_bayar' => $status_bayar,
                ]);

                // Jika Lunas, Buatkan Data Pembayaran dan Arus Kas
                if ($status_bayar == 'lunas') {
                    // Tgl Bayar (Pertengahan bulan)
                    $tgl_bayar = Carbon::createFromDate($tahun, $bulan, rand(10, 25));

                    $pembayaran = Pembayaran::create([
                        'tagihan_id' => $tagihan->id,
                        'pelanggan_id' => $plg->id,
                        'akun_kas_id' => $kasUtama->id,
                        'nominal_bayar' => $total_tagihan,
                        'tanggal_bayar' => $tgl_bayar,
                        'keterangan' => 'Pelunasan Tagihan Warga ' . $plg->kode_pelanggan,
                        'status_validasi' => 'disetujui',
                        'dicatat_oleh' => $adminUser->id,
                    ]);

                    ArusKas::create([
                        'akun_kas_id' => $kasUtama->id,
                        'jenis_transaksi' => 'masuk',
                        'nominal' => $total_tagihan,
                        'keterangan' => 'Pembayaran Air ' . $plg->kode_pelanggan . ' - Bulan ' . $bulan,
                        'tanggal' => $tgl_bayar,
                        'dicatat_oleh' => $adminUser->id,
                        'pembayaran_id' => $pembayaran->id,
                    ]);

                    $totalSaldoKas += $total_tagihan;
                }
            }
        }

        // 6. Update Saldo Kas Akhir
        $kasUtama->update(['saldo' => $totalSaldoKas]);
    }
}
