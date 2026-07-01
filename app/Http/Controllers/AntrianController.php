<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Antrian;
use App\Services\AntrianService;
use App\Events\LockDilepas;

class AntrianController extends Controller
{
    public function __construct(private AntrianService $antrianService) {}

    // Dipanggil frontend setelah audio TTS selesai diputar
    public function lepasLock(Request $request)
    {
        $this->antrianService->lepasLock();

        // Broadcast ke semua client: lock dilepas, tombol panggil aktif kembali
        broadcast(new LockDilepas());

        return response()->json(['message' => 'Lock dilepas.']);
    }

    // Status antrian hari ini — untuk semua meja & display TV
    public function status()
    {
        return response()->json([
            'menunggu'            => Antrian::hariIni()->menunggu()->count(),
            'menunggu_pembayaran' => Antrian::hariIni()->menungguPembayaran()->count(),
            'selesai'             => Antrian::hariIni()->where('status', 'selesai')->count(),
            'lock_aktif'          => app(AntrianService::class)->isLockAktif(),
        ]);
    }
}
