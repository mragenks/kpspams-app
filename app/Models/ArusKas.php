<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArusKas extends Model
{
    protected $table = 'arus_kas';

    protected $fillable = [
        'akun_kas_id',
        'jenis_transaksi',
        'nominal',
        'keterangan',
        'no_bukti',
        'tanggal',
        'dicatat_oleh',
        'pembayaran_id'
    ];

    public function akunKas()
    {
        return $this->belongsTo(AkunKas::class, 'akun_kas_id');
    }

    public function pembayaran()
    {
        return $this->belongsTo(Pembayaran::class);
    }

    public function dicatatOleh()
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }
}
