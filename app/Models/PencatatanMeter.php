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
        $biayaPemakaian = 0;

        if ($volume <= 0) {
            return 0;
        }

        if ($volume <= 10) {
            $biayaPemakaian = 10000;
        } elseif ($volume <= 20) {
            $biayaPemakaian = 10000 + (($volume - 10) * 2200);
        } elseif ($volume <= 30) {
            $biayaPemakaian = 10000 + (10 * 2200) + (($volume - 20) * 2500);
        } else {
            $biayaPemakaian = 10000 + (10 * 2200) + (10 * 2500) + (($volume - 30) * 3000);
        }

        return $biayaPemakaian;
    }

    public function hitungTotalTagihan(): float
    {
        return $this->hitungBiayaPemakaian() + 5000;
    }
}
