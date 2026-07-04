<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Antrian extends Model
{
    protected $fillable = [
        'no_antrian',
        'tanggal',
        'santri_id',
        'status',
        'meja_layanan_id',
        'dipanggil_oleh_layanan_id',
        'meja_kesehatan_id',
        'dipanggil_oleh_kesehatan_id',
        'meja_pembayaran_id',
        'dipanggil_oleh_kasir_id',
        'waktu_daftar',
        'waktu_dipanggil_layanan',
        'waktu_mulai_layanan',
        'waktu_selesai_layanan',
        'waktu_dipanggil_kesehatan',
        'waktu_mulai_kesehatan',
        'waktu_selesai_kesehatan',
        'waktu_dipanggil_pembayaran',
        'waktu_selesai_pembayaran',
    ];

    protected $casts = [
        'tanggal'                    => 'date',
        'waktu_daftar'               => 'datetime',
        'waktu_dipanggil_layanan'    => 'datetime',
        'waktu_mulai_layanan'        => 'datetime',
        'waktu_selesai_layanan'      => 'datetime',
        'waktu_dipanggil_kesehatan'  => 'datetime',
        'waktu_mulai_kesehatan'      => 'datetime',
        'waktu_selesai_kesehatan'    => 'datetime',
        'waktu_dipanggil_pembayaran' => 'datetime',
        'waktu_selesai_pembayaran'   => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->tanggal)) {
                $model->tanggal = today();
            }
        });
    }

    // ── Relationships ──────────────────────────────────────────────────

    public function santri()
    {
        return $this->belongsTo(Santri::class);
    }

    public function mejaLayanan()
    {
        return $this->belongsTo(Meja::class, 'meja_layanan_id');
    }

    public function mejaKesehatan()
    {
        return $this->belongsTo(Meja::class, 'meja_kesehatan_id');
    }

    public function mejaPembayaran()
    {
        return $this->belongsTo(Meja::class, 'meja_pembayaran_id');
    }

    public function dipanggilOlehLayanan()
    {
        return $this->belongsTo(User::class, 'dipanggil_oleh_layanan_id');
    }

    public function dipanggilOlehKesehatan()
    {
        return $this->belongsTo(User::class, 'dipanggil_oleh_kesehatan_id');
    }

    public function dipanggilOlehKasir()
    {
        return $this->belongsTo(User::class, 'dipanggil_oleh_kasir_id');
    }

    public function logs()
    {
        return $this->hasMany(AntrianLog::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────

    public function scopeHariIni($query)
    {
        return $query->whereDate('tanggal', today());
    }

    public function scopeMenunggu($query)
    {
        return $query->where('status', 'menunggu');
    }

    public function scopeMenungguKesehatan($query)
    {
        return $query->where('status', 'menunggu_kesehatan');
    }

    public function scopeMenungguPembayaran($query)
    {
        return $query->where('status', 'menunggu_pembayaran');
    }

    // ── Helpers ───────────────────────────────────────────────────────

    // Cek apakah antrian ini masih bisa diambil
    public function isBisaDiambil(): bool
    {
        return $this->status === 'menunggu';
    }

    // Cek apakah sedang diproses layanan
    public function isSedangDiproses(): bool
    {
        return in_array($this->status, ['dipanggil_layanan', 'diproses_layanan']);
    }
}
