<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Antrian;
use App\Models\AntrianLog;
use App\Services\AntrianService;
use App\Events\AntrianDipanggil;
use App\Events\AntrianStatusUpdate;

class PembayaranController extends Controller
{
    public function __construct(private AntrianService $antrianService) {}

    public function index(Request $request)
    {
        $meja = $request->sesi_meja;

        $antrianAktif = Antrian::hariIni()
            ->where('meja_pembayaran_id', $meja->id)
            ->whereIn('status', ['dipanggil_pembayaran'])
            ->with('santri')
            ->first();

        $totalMenunggu = Antrian::hariIni()->menungguPembayaran()->count();
        $lockAktif     = $this->antrianService->isLockAktif();

        return view('pembayaran.index', compact('antrianAktif', 'totalMenunggu', 'lockAktif', 'meja'));
    }

    // Ambil antrian pembayaran berikutnya (urut waktu_selesai_layanan)
    public function ambilAntrian(Request $request)
    {
        $meja = $request->sesi_meja;

        $sudahAda = Antrian::hariIni()
            ->where('meja_pembayaran_id', $meja->id)
            ->where('status', 'dipanggil_pembayaran')
            ->exists();

        if ($sudahAda) {
            return response()->json([
                'message' => 'Selesaikan pembayaran yang sedang berjalan terlebih dahulu.',
            ], 422);
        }

        $antrian = $this->antrianService->ambilAntrianPembayaran();

        if (!$antrian) {
            return response()->json(['message' => 'Tidak ada antrian pembayaran.'], 404);
        }

        $antrian->update([
            'status'                    => 'dipanggil_pembayaran',
            'meja_pembayaran_id'        => $meja->id,
            'dipanggil_oleh_kasir_id'   => $request->sesi_user->id,
            'waktu_dipanggil_pembayaran' => now(),
        ]);

        AntrianLog::create([
            'antrian_id'     => $antrian->id,
            'status_sebelum' => 'menunggu_pembayaran',
            'status_sesudah' => 'dipanggil_pembayaran',
            'meja_id'        => $meja->id,
            'user_id'        => $request->sesi_user->id,
        ]);

        return response()->json([
            'message' => 'Antrian pembayaran berhasil diambil.',
            'antrian' => $antrian->load('santri'),
        ]);
    }

    // Panggil santri ke meja pembayaran via audio
    public function panggilAntrian(Request $request, Antrian $antrian)
    {
        $meja = $request->sesi_meja;

        if ($this->antrianService->isLockAktif()) {
            return response()->json([
                'message' => 'Meja lain sedang memanggil. Harap tunggu.',
            ], 423);
        }

        $this->antrianService->buatLock($antrian->id, $meja->id, $request->sesi_user->id);

        broadcast(new AntrianDipanggil($antrian->load('santri'), $meja));

        return response()->json([
            'message'        => 'Pemanggilan dimulai.',
            'teks_panggilan' => "Nomor antrian {$antrian->no_antrian}, {$antrian->santri->nama}, silakan menuju {$meja->nama_meja} untuk pembayaran.",
        ]);
    }

    // Selesai pembayaran
    public function selesai(Request $request, Antrian $antrian)
    {
        if ($antrian->status !== 'dipanggil_pembayaran') {
            return response()->json(['message' => 'Status antrian tidak valid.'], 422);
        }

        $antrian->update([
            'status'                  => 'selesai',
            'waktu_selesai_pembayaran' => now(),
        ]);

        // Update status santri jadi selesai
        $antrian->santri->update(['status' => 'selesai']);

        AntrianLog::create([
            'antrian_id'     => $antrian->id,
            'status_sebelum' => 'dipanggil_pembayaran',
            'status_sesudah' => 'selesai',
            'meja_id'        => $request->sesi_meja->id,
            'user_id'        => $request->sesi_user->id,
        ]);

        broadcast(new AntrianStatusUpdate($antrian));

        return response()->json(['message' => 'Pembayaran selesai.']);
    }
}
