<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $table = 'pembayaran';

    protected $fillable = [
        'tagihan_id',
        'pelanggan_id',
        'akun_kas_id',
        'nominal_bayar',
        'tanggal_bayar',
        'keterangan',
        'foto_bukti',
        'is_pending',
        'dicatat_oleh'
    ];

    protected $casts = [
        'is_pending' => 'boolean',
    ];

    public function tagihan()
    {
        return $this->belongsTo(TagihanAir::class, 'tagihan_id');
    }

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class);
    }

    public function akunKas()
    {
        return $this->belongsTo(AkunKas::class, 'akun_kas_id');
    }

    public function arusKas()
    {
        return $this->hasOne(ArusKas::class);
    }
}
