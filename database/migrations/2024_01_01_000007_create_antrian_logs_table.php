<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Log setiap perubahan status antrian.
     * Berguna untuk:
     * - Audit trail ("siapa yang batalkan antrian ini?")
     * - Laporan harian (rata-rata waktu tunggu, jumlah dilayani per meja, dll)
     * - Debug jika ada masalah urutan antrian
     */
    public function up(): void
    {
        Schema::create('antrian_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('antrian_id')
                  ->constrained('antrians')
                  ->cascadeOnDelete();

            $table->enum('status_sebelum', [
                'menunggu',
                'dipanggil_layanan',
                'diproses_layanan',
                'menunggu_pembayaran',
                'dipanggil_pembayaran',
                'selesai',
                'batal',
            ])->nullable(); // null = baru dibuat (status awal)

            $table->enum('status_sesudah', [
                'menunggu',
                'dipanggil_layanan',
                'diproses_layanan',
                'menunggu_pembayaran',
                'dipanggil_pembayaran',
                'selesai',
                'batal',
            ]);

            // Siapa yang melakukan perubahan status
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // Dari meja mana aksi dilakukan
            $table->foreignId('meja_id')
                  ->nullable()
                  ->constrained('meja')
                  ->nullOnDelete();

            // Catatan tambahan (opsional, misal alasan pembatalan)
            $table->string('keterangan')->nullable();

            $table->timestamp('created_at')->useCurrent();

            // Index untuk query laporan harian
            $table->index(['antrian_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('antrian_logs');
    }
};
