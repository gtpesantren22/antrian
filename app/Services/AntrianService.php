<?php

namespace App\Services;

use App\Models\Antrian;
use App\Models\PemanggilanLock;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AntrianService
{
    /**
     * Generate nomor antrian berikutnya untuk hari ini.
     * Format: A-001, A-002, ... A-999
     * Reset setiap hari.
     *
     * Dibungkus transaction untuk mencegah race condition
     * saat dua resepsionis input bersamaan.
     */
    public function generateNoAntrian(): string
    {
        return DB::transaction(function () {
            $lastNo = Antrian::whereDate('tanggal', today())
                ->lockForUpdate()           // <-- row lock, cegah duplikat
                ->max('no_antrian');

            if ($lastNo === null) {
                $next = 1;
            } else {
                // Ambil angka dari "A-001" → 1
                $next = (int) substr($lastNo, 2) + 1;
            }

            return 'A-' . str_pad($next, 3, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Ambil antrian terkecil yang statusnya 'menunggu' hari ini.
     * Dibungkus transaction + lockForUpdate untuk cegah dua petugas
     * mendapat nomor yang sama saat klik bersamaan.
     */
    public function ambilAntrianLayanan(): ?Antrian
    {
        return DB::transaction(function () {
            return Antrian::whereDate('tanggal', today())
                ->where('status', 'menunggu')
                ->orderBy('no_antrian', 'asc')
                ->lockForUpdate()
                ->first();
        });
    }

    /**
     * Ambil antrian berikutnya untuk meja kesehatan.
     * Urutan berdasarkan waktu_selesai_layanan (siapa lebih dulu selesai layanan).
     */
    public function ambilAntrianKesehatan(): ?Antrian
    {
        return DB::transaction(function () {
            return Antrian::whereDate('tanggal', today())
                ->where('status', 'menunggu_kesehatan')
                ->orderBy('waktu_selesai_layanan', 'asc')
                ->lockForUpdate()
                ->first();
        });
    }

    /**
     * Ambil antrian berikutnya untuk meja pembayaran.
     * Urutan berdasarkan waktu_selesai_kesehatan (siapa lebih dulu selesai kesehatan).
     */
    public function ambilAntrianPembayaran(): ?Antrian
    {
        return DB::transaction(function () {
            return Antrian::whereDate('tanggal', today())
                ->where('status', 'menunggu_pembayaran')
                ->orderBy('waktu_selesai_kesehatan', 'asc')
                ->lockForUpdate()
                ->first();
        });
    }

    /**
     * Cek apakah sedang ada lock pemanggilan aktif.
     * Mempertimbangkan expired_at sebagai safety net.
     */
    public function isLockAktif(): bool
    {
        return PemanggilanLock::where('is_active', true)
            ->where('expired_at', '>', now())
            ->exists();
    }

    /**
     * Buat lock pemanggilan baru.
     * Dipanggil sesaat sebelum broadcast event panggil.
     *
     * @param int $antrianId
     * @param int $mejaId
     * @param int $userId
     * @param int $durasiDetik  Berapa lama lock aktif (default 30 detik)
     */
    public function buatLock(int $antrianId, int $mejaId, int $userId, int $durasiDetik = 30): PemanggilanLock
    {
        // Nonaktifkan lock lama yang mungkin masih tersisa (defensive)
        PemanggilanLock::where('is_active', true)->update(['is_active' => false]);

        return PemanggilanLock::create([
            'antrian_id' => $antrianId,
            'meja_id'    => $mejaId,
            'user_id'    => $userId,
            'is_active'  => true,
            'expired_at' => now()->addSeconds($durasiDetik),
        ]);
    }

    /**
     * Lepas lock pemanggilan.
     * Dipanggil dari frontend setelah audio TTS selesai diputar.
     */
    public function lepasLock(): void
    {
        PemanggilanLock::where('is_active', true)
            ->update(['is_active' => false]);
    }

    /**
     * Proses saat petugas memilih meja + input PIN.
     * Return session_token kalau berhasil, null kalau PIN salah.
     */
    public function loginMeja(int $mejaId, string $pin): ?string
    {
        $user = \App\Models\User::where('meja_id', $mejaId)
            ->where('is_active', true)
            ->first();

        if (!$user || !\Hash::check($pin, $user->pin)) {
            return null;
        }

        // Nonaktifkan sesi lama di meja yang sama (kalau ada)
        \App\Models\MejaSesi::where('meja_id', $mejaId)
            ->where('expired_at', '>', now())
            ->update(['expired_at' => now()]);

        $token = \Str::random(64);

        \App\Models\MejaSesi::create([
            'meja_id'          => $mejaId,
            'user_id'          => $user->id,
            'session_token'    => $token,
            'expired_at'       => now()->addHours(12), // sesi bertahan 12 jam
            'last_activity_at' => now(),
        ]);

        return $token;
    }

    /**
     * Validasi token dari cookie/header setiap request.
     * Sekaligus perpanjang sesi (sliding session).
     */
    public function validateSesi(string $token): ?\App\Models\MejaSesi
    {
        $sesi = \App\Models\MejaSesi::with(['meja', 'user'])
            ->where('session_token', $token)
            ->where('expired_at', '>', now())
            ->first();

        if (!$sesi) {
            return null;
        }

        // Perpanjang sesi selama masih aktif digunakan
        $sesi->update([
            'expired_at'       => now()->addHours(12),
            'last_activity_at' => now(),
        ]);

        return $sesi;
    }

    /**
     * Logout — petugas selesai shift atau ganti petugas di meja yang sama.
     */
    public function logoutMeja(string $token): void
    {
        \App\Models\MejaSesi::where('session_token', $token)
            ->update(['expired_at' => now()]);
    }
}
