<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AkunKas extends Model
{
    protected $table = 'akun_kas';

    protected $fillable = [
        'nama_akun',
        'jenis_akun',
        'saldo',
        'keterangan',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'saldo' => 'decimal:2',
    ];

    public function arusKas()
    {
        return $this->hasMany(ArusKas::class);
    }

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class);
    }
}
