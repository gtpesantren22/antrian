<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meja', function (Blueprint $table) {
            $table->id();
            $table->string('nama_meja');               // "Meja Layanan 1", "Meja Pembayaran", dll
            $table->enum('tipe', [
                'resepsionis',
                'layanan',
                'kesehatan',
                'pembayaran',
            ]);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed data awal meja
        DB::table('meja')->insert([
            ['nama_meja' => 'Meja Resepsionis',  'tipe' => 'resepsionis', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nama_meja' => 'Operator 1',    'tipe' => 'layanan',     'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nama_meja' => 'Operator 2',    'tipe' => 'layanan',     'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nama_meja' => 'Operator 3',    'tipe' => 'layanan',     'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nama_meja' => 'Operator 4',    'tipe' => 'layanan',     'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nama_meja' => 'Meja Kesehatan 1',  'tipe' => 'kesehatan',   'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nama_meja' => 'Meja Kesehatan 2',  'tipe' => 'kesehatan',   'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nama_meja' => 'Meja Pembayaran 1', 'tipe' => 'pembayaran',  'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nama_meja' => 'Meja Pembayaran 2', 'tipe' => 'pembayaran',  'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nama_meja' => 'Meja Pembayaran 3', 'tipe' => 'pembayaran',  'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nama_meja' => 'Meja Pembayaran 4', 'tipe' => 'pembayaran',  'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('meja');
    }
};
