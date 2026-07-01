<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('santri', function (Blueprint $table) {
            $table->id();
            $table->string('no_induk')->unique()->nullable(); // NIS / nomor induk santri
            $table->string('nama');
            $table->string('nama_ayah')->nullable();
            $table->string('asal_daerah')->nullable();
            $table->string('no_hp')->nullable();             // HP wali/santri
            $table->string('kamar')->nullable();             // diisi saat proses administrasi selesai
            $table->enum('status', [
                'terdaftar',     // sudah ada di list, belum datang
                'hadir',         // sedang/sudah proses antrian hari ini
                'selesai',       // semua proses (adm + bayar) sudah selesai
            ])->default('terdaftar');
            $table->timestamps();

            // Index untuk mempercepat pencarian nama di resepsionis
            $table->index('nama');
            $table->index('no_induk');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('santri');
    }
};
