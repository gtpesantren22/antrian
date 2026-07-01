<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AntrianLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'antrian_id',
        'status_sebelum',
        'status_sesudah',
        'user_id',
        'meja_id',
        'keterangan',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function antrian()
    {
        return $this->belongsTo(Antrian::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function meja()
    {
        return $this->belongsTo(Meja::class);
    }
}
