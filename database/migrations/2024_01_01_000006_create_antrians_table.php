<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ALUR STATUS:
     *
     *  [resepsionis input] → menunggu
     *       ↓
     *  [petugas layanan ambil] → dipanggil_layanan
     *       ↓
     *  [petugas layanan mulai proses] → diproses_layanan
     *       ↓
     *  [petugas layanan selesai] → menunggu_pembayaran
     *       ↓
     *  [kasir ambil] → dipanggil_pembayaran
     *       ↓
     *  [kasir selesai] → selesai
     *
     *  Bisa juga: batal (dibatalkan kapan saja sebelum selesai)
     */
    public function up(): void
    {
        Schema::create('antrians', function (Blueprint $table) {
            $table->id();

            // ── Identitas antrian ──────────────────────────────────────────
            $table->string('no_antrian', 10);
            // Format: "A-001". Reset harian, index tanggal+no agar unik per hari
            $table->date('tanggal');

            $table->foreignId('santri_id')
                  ->constrained('santri')
                  ->cascadeOnDelete();

            // ── Status ────────────────────────────────────────────────────
            $table->enum('status', [
                'menunggu',
                'dipanggil_layanan',
                'diproses_layanan',
                'menunggu_pembayaran',
                'dipanggil_pembayaran',
                'selesai',
                'batal',
            ])->default('menunggu');

            // ── Meja & Petugas ────────────────────────────────────────────
            // Meja layanan yang menangani (diisi saat dipanggil_layanan)
            $table->foreignId('meja_layanan_id')
                  ->nullable()
                  ->constrained('meja')
                  ->nullOnDelete();

            // Petugas yang melakukan pemanggilan layanan
            $table->foreignId('dipanggil_oleh_layanan_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // Meja pembayaran yang menangani (diisi saat dipanggil_pembayaran)
            $table->foreignId('meja_pembayaran_id')
                  ->nullable()
                  ->constrained('meja')
                  ->nullOnDelete();

            // Petugas kasir yang melakukan pemanggilan pembayaran
            $table->foreignId('dipanggil_oleh_kasir_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // ── Timestamps proses ─────────────────────────────────────────
            // Diisi oleh resepsionis saat cetak struk
            $table->timestamp('waktu_daftar')->useCurrent();

            // Saat petugas layanan klik "panggil"
            $table->timestamp('waktu_dipanggil_layanan')->nullable();

            // Saat petugas layanan klik "mulai proses"
            $table->timestamp('waktu_mulai_layanan')->nullable();

            // Saat petugas layanan klik "selesai" → masuk pool pembayaran
            // INI kunci urutan antrian pembayaran (ORDER BY kolom ini ASC)
            $table->timestamp('waktu_selesai_layanan')->nullable();

            // Saat kasir klik "panggil"
            $table->timestamp('waktu_dipanggil_pembayaran')->nullable();

            // Saat kasir klik "selesai"
            $table->timestamp('waktu_selesai_pembayaran')->nullable();

            $table->timestamps();

            // ── Indexes ───────────────────────────────────────────────────
            // Cari antrian hari ini yang statusnya menunggu (sering diquery)
            $table->index(['tanggal', 'status']);

            // Untuk ORDER BY no_antrian (ambil terkecil yang belum dilayani)
            $table->index(['tanggal', 'status', 'no_antrian']);

            // Urutan pool pembayaran
            $table->index(['tanggal', 'status', 'waktu_selesai_layanan']);

            // Unique: satu no_antrian tidak boleh duplikat di tanggal yang sama
            $table->unique(['tanggal', 'no_antrian']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('antrians');
    }
};
