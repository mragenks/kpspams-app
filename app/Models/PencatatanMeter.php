<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PencatatanMeter extends Model
{
    protected $table = 'pencatatan_meter';

    protected $fillable = [
        'pelanggan_id',
        'bulan_periode',
        'tahun_periode',
        'meter_awal',
        'meter_akhir',
        'tanggal_pencatatan',
        'foto_meteran',
        'dicatat_oleh'
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class);
    }

    public function tagihanAir()
    {
        return $this->hasOne(TagihanAir::class, 'pencatatan_meter_id');
    }

    public function hitungBiayaPemakaian()
    {
        $tarif = \App\Models\PengaturanTarif::first();
        $volume = $this->meter_akhir - $this->meter_awal;
        $biaya = 0;

        if ($volume > 0) {
            // Blok 1: 0 - 5 m3 (Dikenakan flat Rp 10.000)
            $biaya += $tarif->tarif_0_5;
        }

        if ($volume > 5) {
            // Blok 2: 6 - 10 m3 (Dikenakan flat Rp 10.000 tambahan)
            $biaya += $tarif->tarif_6_10;
        }

        if ($volume > 10) {
            // Blok 3: 11 - 20 m3 (Dihitung per m3)
            $sisa = min($volume, 20) - 10;
            $biaya += $sisa * $tarif->tarif_11_20;
        }

        if ($volume > 20) {
            // Blok 4: 21 - 30 m3 (Dihitung per m3)
            $sisa = min($volume, 30) - 20;
            $biaya += $sisa * $tarif->tarif_21_30;
        }

        if ($volume > 30) {
            // Blok 5: > 30 m3 (Dihitung per m3)
            $sisa = $volume - 30;
            $biaya += $sisa * $tarif->tarif_atas_30;
        }

        return $biaya;
    }

    public function hitungTotalTagihan(): float
    {
        return $this->hitungBiayaPemakaian() + 5000;
    }
}
