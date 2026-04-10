<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengurus extends Model
{
    protected $table = 'pengurus';

    protected $fillable = [
        'user_id',
        'nama_pengurus',
        'jabatan',
        'no_hp'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
