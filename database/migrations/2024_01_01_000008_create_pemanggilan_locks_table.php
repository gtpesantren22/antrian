<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel lock pemanggilan — mencegah bentrok suara antar meja.
     *
     * Cara kerja:
     * 1. Saat petugas klik "panggil", backend cek tabel ini
     * 2. Jika ada record dengan is_active = true, tolak pemanggilan
     * 3. Jika tidak ada, insert record baru (is_active = true)
     * 4. Broadcast event ke semua client → tombol panggil di meja lain disabled
     * 5. Setelah audio selesai (frontend kirim sinyal), set is_active = false
     * 6. Broadcast event "lock dilepas" → tombol aktif kembali
     *
     * expired_at diisi sebagai safety net: kalau frontend crash dan tidak sempat
     * melepas lock, lock otomatis dianggap expired setelah N detik.
     * Backend harus cek: is_active = true AND expired_at > NOW()
     */
    public function up(): void
    {
        Schema::create('pemanggilan_locks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('antrian_id')
                  ->constrained('antrians')
                  ->cascadeOnDelete();

            $table->foreignId('meja_id')
                  ->constrained('meja')
                  ->cascadeOnDelete();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->boolean('is_active')->default(true);

            // Safety net: lock otomatis expired setelah 30 detik
            // (lebih dari cukup untuk audio TTS selesai diputar)
            $table->timestamp('expired_at');

            $table->timestamp('created_at')->useCurrent();

            $table->index(['is_active', 'expired_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemanggilan_locks');
    }
};
