<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    protected $table = 'pelanggan';

    protected $fillable = [
        'user_id',
        'kode_pelanggan',
        'nik',
        'status',
        'saldo',
        'meteran_awal'
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

    public function getNamaPelangganAttribute()
    {
        return $this->user ? $this->user->nama_lengkap : 'Tanpa Nama';
    }

    public function getNoHpAttribute()
    {
        return $this->user ? $this->user->no_hp : null;
    }

    public function getAlamatAttribute()
    {
        return $this->user ? $this->user->alamat : null;
    }
}
