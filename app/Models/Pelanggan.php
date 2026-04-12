<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    protected $table = 'pelanggan';

    protected $fillable = [
        'user_id',
        'kode_pelanggan',
        'nama_pelanggan',
        'nik',
        'alamat',
        'no_hp',
        'status',
        'meteran_awal',
        'saldo'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pencatatanMeter()
    {
        return $this->hasMany(PencatatanMeter::class);
    }

    public function tagihanAir()
    {
        return $this->hasMany(TagihanAir::class);
    }

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class);
    }
}
