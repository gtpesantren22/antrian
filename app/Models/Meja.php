<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meja extends Model
{
    protected $table = 'meja';

    protected $fillable = ['nama_meja', 'tipe', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Meja sedang dipakai siapa
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Sesi aktif di meja ini
    public function sesiAktif()
    {
        return $this->hasOne(MejaSesi::class)
            ->where('expired_at', '>', now())
            ->latest();
    }

    // Scope: hanya meja yang aktif
    public function scopeAktif($query)
    {
        return $query->where('is_active', true);
    }

    // Cek apakah meja sedang ditempati
    public function isSedangDitempati(): bool
    {
        return $this->sesiAktif()->exists();
    }
}
