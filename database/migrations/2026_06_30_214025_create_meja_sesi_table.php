<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('meja_sesi', function (Blueprint $table) {
            $table->id();

            $table->foreignId('meja_id')
                ->constrained('meja')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Token unik per browser/device, disimpan juga di cookie/localStorage
            $table->string('session_token')->unique();

            // Safety net: sesi otomatis expired kalau tidak ada aktivitas
            $table->timestamp('expired_at');

            // Kapan terakhir ada request dari device ini (untuk perpanjang sesi)
            $table->timestamp('last_activity_at')->useCurrent();

            $table->timestamp('created_at')->useCurrent();

            $table->index(['meja_id', 'expired_at']);
            $table->index('session_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meja_sesi');
    }
};
