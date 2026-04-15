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
        $volume = $this->meter_akhir - $this->meter_awal;
        if ($volume <= 0) return 0;

        // Ambil dari database
        $tarif = \App\Models\PengaturanTarif::first();

        $tarifBatasBawah = $tarif->tarif_batas_bawah ?? 10000;
        $tarif11_20 = $tarif->tarif_11_20 ?? 2200;
        $tarif21_30 = $tarif->tarif_21_30 ?? 2500;
        $tarifAtas30 = $tarif->tarif_atas_30 ?? 3000;

        if ($volume <= 10) {
            return $tarifBatasBawah;
        } elseif ($volume <= 20) {
            return $tarifBatasBawah + (($volume - 10) * $tarif11_20);
        } elseif ($volume <= 30) {
            return $tarifBatasBawah + (10 * $tarif11_20) + (($volume - 20) * $tarif21_30);
        } else {
            return $tarifBatasBawah + (10 * $tarif11_20) + (10 * $tarif21_30) + (($volume - 30) * $tarifAtas30);
        }
    }

    public function hitungTotalTagihan(): float
    {
        return $this->hitungBiayaPemakaian() + 5000;
    }
}
