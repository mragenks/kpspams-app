<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatInventaris extends Model
{
    protected $table = 'riwayat_inventaris';

    protected $fillable = [
        'inventaris_id',
        'jenis_mutasi',
        'jumlah',
        'stok_sebelum',
        'stok_sesudah',
        'keterangan',
        'tanggal',
        'dicatat_oleh'
    ];

    public function inventaris()
    {
        return $this->belongsTo(Inventaris::class);
    }

    public function dicatatOleh()
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }
}
