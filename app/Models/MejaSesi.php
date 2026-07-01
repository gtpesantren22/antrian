<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MejaSesi extends Model
{
    public $timestamps = false;

    protected $table = 'meja_sesi';

    protected $fillable = [
        'meja_id',
        'user_id',
        'session_token',
        'expired_at',
        'last_activity_at',
    ];

    protected $casts = [
        'expired_at'       => 'datetime',
        'last_activity_at' => 'datetime',
    ];

    public function meja()
    {
        return $this->belongsTo(Meja::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Cek apakah sesi masih valid
    public function isAktif(): bool
    {
        return $this->expired_at->isFuture();
    }
}
