<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PemanggilanLock extends Model
{
    public $timestamps = false;

    protected $table = 'pemanggilan_locks';

    protected $fillable = [
        'antrian_id',
        'meja_id',
        'user_id',
        'is_active',
        'expired_at',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'expired_at' => 'datetime',
    ];

    public function antrian()
    {
        return $this->belongsTo(Antrian::class);
    }

    public function meja()
    {
        return $this->belongsTo(Meja::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scope: hanya lock yang benar-benar aktif
    public function scopeAktif($query)
    {
        return $query->where('is_active', true)
            ->where('expired_at', '>', now());
    }
}
