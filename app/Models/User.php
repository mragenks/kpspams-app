<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'nama_lengkap',
        'username',
        'email',
        'password',
        'no_hp',
        'alamat',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    public function pengurus()
    {
        return $this->hasOne(Pengurus::class);
    }

    public function pelanggan()
    {
        return $this->hasOne(Pelanggan::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isKetua(): bool
    {
        return $this->role === 'ketua';
    }

    public function isPelanggan(): bool
    {
        return $this->role === 'pelanggan';
    }
}
