<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanTarif extends Model
{
    protected $table = 'pengaturan_tarif';
    protected $guarded = [];

    public function pengurus()
    {
        return $this->belongsTo(Pengurus::class, 'diupdate_oleh', 'id');
    }
}
