<?php

use App\Http\Controllers\AntrianController;
use App\Http\Controllers\DisplayController;
use App\Http\Controllers\LayananController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\ResepsionisController;
use App\Http\Controllers\SesiController;
use Illuminate\Support\Facades\Route;

// ── Publik ────────────────────────────────────────────────────────────
Route::get('/', [SesiController::class, 'index'])->name('pilih.meja');
Route::post('/login', [SesiController::class, 'login'])->name('sesi.login');
Route::post('/logout', [SesiController::class, 'logout'])->name('sesi.logout');

// Display antrian (layar TV) — bebas akses
Route::get('/display', [DisplayController::class, 'index'])->name('display');

// ── Operasional — wajib sesi ─────────────────────────────────────────
Route::middleware('validasi.sesi')->group(function () {

    // Resepsionis
    Route::middleware('tipe.meja:resepsionis')
        ->prefix('resepsionis')
        ->name('resepsionis.')
        ->group(function () {
            Route::get('/', [ResepsionisController::class, 'index'])->name('index');
            Route::get('/cari-santri', [ResepsionisController::class, 'cariSantri'])->name('cari');
            Route::post('/tambah-antrian/{santri}', [ResepsionisController::class, 'tambahAntrian'])->name('tambah');
            Route::post('/batal-antrian/{antrian}', [ResepsionisController::class, 'batalAntrian'])->name('batal');
            Route::post('/sync-santri', [ResepsionisController::class, 'syncSantri'])->name('sync');
        });

    // Layanan
    Route::middleware('tipe.meja:layanan')
        ->prefix('layanan')
        ->name('layanan.')
        ->group(function () {
            Route::get('/', [LayananController::class, 'index'])->name('index');
            Route::post('/ambil-antrian', [LayananController::class, 'ambilAntrian'])->name('ambil');
            Route::post('/panggil/{antrian}', [LayananController::class, 'panggilAntrian'])->name('panggil');
            Route::post('/mulai/{antrian}', [LayananController::class, 'mulaiProses'])->name('mulai');
            Route::post('/selesai/{antrian}', [LayananController::class, 'selesai'])->name('selesai');
        });

    // Pembayaran
    Route::middleware('tipe.meja:pembayaran')
        ->prefix('pembayaran')
        ->name('pembayaran.')
        ->group(function () {
            Route::get('/', [PembayaranController::class, 'index'])->name('index');
            Route::post('/ambil-antrian', [PembayaranController::class, 'ambilAntrian'])->name('ambil');
            Route::post('/panggil/{antrian}', [PembayaranController::class, 'panggilAntrian'])->name('panggil');
            Route::post('/selesai/{antrian}', [PembayaranController::class, 'selesai'])->name('selesai');
        });

    // Shared — semua meja boleh akses
    Route::post('/antrian/lepas-lock', [AntrianController::class, 'lepasLock'])->name('antrian.lepasLock');
    Route::get('/antrian/status', [AntrianController::class, 'status'])->name('antrian.status');
});