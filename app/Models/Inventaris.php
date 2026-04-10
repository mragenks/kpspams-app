<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventaris extends Model
{
    protected $table = 'inventaris';

    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'jenis_aset',
        'stok_awal',
        'stok_saat_ini',
        'kondisi',
        'lokasi',
        'tanggal_perolehan',
        'keterangan'
    ];

    public function riwayat()
    {
        return $this->hasMany(RiwayatInventaris::class);
    }
}
