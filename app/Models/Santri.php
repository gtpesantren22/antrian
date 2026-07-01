<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Santri extends Model
{
    protected $table = 'santri';

    protected $fillable = [
        'no_induk',
        'nama',
        'nama_ayah',
        'asal_daerah',
        'no_hp',
        'kamar',
        'status',
    ];

    public function antrians()
    {
        return $this->hasMany(Antrian::class);
    }

    // Antrian aktif hari ini (kalau ada)
    public function antrianHariIni()
    {
        return $this->hasOne(Antrian::class)
            ->whereDate('tanggal', today());
    }

    // Scope: cari nama atau no_induk (untuk fitur search di resepsionis)
    public function scopeCari($query, string $keyword)
    {
        return $query->where('nama', 'like', "%{$keyword}%")
            ->orWhere('no_induk', 'like', "%{$keyword}%");
    }
}
