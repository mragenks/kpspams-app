<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengurus extends Model
{
    protected $table = 'pengurus';

    protected $fillable = [
        'user_id',
        'jabatan'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function riwayatPerubahanTarif()
    {
        return $this->hasMany(PengaturanTarif::class, 'diupdate_oleh', 'id');
    }

    public function getNamaPengurusAttribute()
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
