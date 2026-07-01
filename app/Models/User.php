<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = ['nama', 'pin', 'meja_id', 'is_active'];

    protected $hidden = ['pin'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function meja()
    {
        return $this->belongsTo(Meja::class);
    }

    public function sesi()
    {
        return $this->hasMany(MejaSesi::class);
    }

    public function sesiAktif()
    {
        return $this->hasOne(MejaSesi::class)
            ->where('expired_at', '>', now())
            ->latest();
    }
}
