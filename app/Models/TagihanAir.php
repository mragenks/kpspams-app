<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TagihanAir extends Model
{
    protected $table = 'tagihan_air';

    protected $fillable = [
        'pelanggan_id',
        'pencatatan_meter_id',
        'bulan_periode',
        'tahun_periode',
        'biaya_beban',
        'biaya_pemakaian',
        'total_tagihan',
        'total_terbayar',
        'sisa_tagihan',
        'status_bayar'
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class);
    }

    public function pencatatanMeter()
    {
        return $this->belongsTo(PencatatanMeter::class, 'pencatatan_meter_id');
    }

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'tagihan_id');
    }
}
