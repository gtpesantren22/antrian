<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Antrian;
use App\Models\AntrianLog;
use App\Models\PemanggilanLock;
use App\Services\AntrianService;
use App\Events\AntrianDipanggil;
use App\Events\AntrianStatusUpdate;

class KesehatanController extends Controller
{
    public function __construct(private AntrianService $antrianService) {}

    public function index(Request $request)
    {
        $meja = $request->sesi_meja;

        // Antrian yang sedang ditangani meja ini (kalau ada)
        $antrianAktif = Antrian::hariIni()
            ->where('meja_kesehatan_id', $meja->id)
            ->whereIn('status', ['dipanggil_kesehatan', 'diproses_kesehatan'])
            ->with('santri')
            ->first();

        // Jumlah antrian menunggu (untuk info di UI)
        $totalMenunggu = Antrian::hariIni()->menungguKesehatan()->count();

        // Lock aktif? (untuk disable tombol panggil saat meja lain sedang memanggil)
        $lockAktif = $this->antrianService->isLockAktif();

        return view('kesehatan.index', compact('antrianAktif', 'totalMenunggu', 'lockAktif', 'meja'));
    }

    // Ambil nomor antrian terkecil yang belum dilayani kesehatan
    public function ambilAntrian(Request $request)
    {
        $meja = $request->sesi_meja;

        // Cek apakah meja ini sudah punya antrian aktif
        $sudahAda = Antrian::hariIni()
            ->where('meja_kesehatan_id', $meja->id)
            ->whereIn('status', ['dipanggil_kesehatan', 'diproses_kesehatan'])
            ->exists();

        if ($sudahAda) {
            return response()->json([
                'message' => 'Selesaikan antrian yang sedang berjalan terlebih dahulu.',
            ], 422);
        }

        $antrian = $this->antrianService->ambilAntrianKesehatan();

        if (!$antrian) {
            return response()->json([
                'message' => 'Tidak ada antrian kesehatan yang menunggu.',
            ], 404);
        }

        $antrian->update([
            'status'                      => 'dipanggil_kesehatan',
            'meja_kesehatan_id'           => $meja->id,
            'dipanggil_oleh_kesehatan_id' => $request->sesi_user->id,
            'waktu_dipanggil_kesehatan'   => now(),
        ]);

        AntrianLog::create([
            'antrian_id'     => $antrian->id,
            'status_sebelum' => 'menunggu_kesehatan',
            'status_sesudah' => 'dipanggil_kesehatan',
            'meja_id'        => $meja->id,
            'user_id'        => $request->sesi_user->id,
        ]);

        return response()->json([
            'message' => 'Antrian berhasil diambil.',
            'antrian' => $antrian->load('santri'),
        ]);
    }

    // Panggil santri via audio TTS — buat lock dulu
    public function panggilAntrian(Request $request, Antrian $antrian)
    {
        $meja = $request->sesi_meja;

        // Tolak kalau ada lock aktif dari meja lain
        if ($this->antrianService->isLockAktif()) {
            return response()->json([
                'message' => 'Meja lain sedang memanggil. Harap tunggu.',
            ], 423); // 423 Locked
        }

        // Buat lock 30 detik
        $this->antrianService->buatLock($antrian->id, $meja->id, $request->sesi_user->id);

        // Broadcast ke semua client: lock aktif + data panggilan
        broadcast(new AntrianDipanggil($antrian->load('santri'), $meja));

        return response()->json([
            'message' => 'Pemanggilan dimulai.',
            'teks_panggilan' => "Nomor antrian {$antrian->no_antrian}, {$antrian->santri->nama}, silakan menuju {$meja->nama_meja}.",
        ]);
    }

    // Mulai proses pemeriksaan kesehatan
    public function mulaiProses(Request $request, Antrian $antrian)
    {
        if ($antrian->status !== 'dipanggil_kesehatan') {
            return response()->json(['message' => 'Status antrian tidak valid.'], 422);
        }

        $antrian->update([
            'status'                 => 'diproses_kesehatan',
            'waktu_mulai_kesehatan' => now(),
        ]);

        AntrianLog::create([
            'antrian_id'     => $antrian->id,
            'status_sebelum' => 'dipanggil_kesehatan',
            'status_sesudah' => 'diproses_kesehatan',
            'meja_id'        => $request->sesi_meja->id,
            'user_id'        => $request->sesi_user->id,
        ]);

        broadcast(new AntrianStatusUpdate($antrian));

        return response()->json(['message' => 'Pemeriksaan kesehatan dimulai.']);
    }

    // Selesai pemeriksaan kesehatan → masuk pool pembayaran (kasir)
    public function selesai(Request $request, Antrian $antrian)
    {
        if ($antrian->status !== 'diproses_kesehatan') {
            return response()->json(['message' => 'Status antrian tidak valid.'], 422);
        }

        $antrian->update([
            'status'                   => 'menunggu_pembayaran',
            'waktu_selesai_kesehatan' => now(),
        ]);

        AntrianLog::create([
            'antrian_id'     => $antrian->id,
            'status_sebelum' => 'diproses_kesehatan',
            'status_sesudah' => 'menunggu_pembayaran',
            'meja_id'        => $request->sesi_meja->id,
            'user_id'        => $request->sesi_user->id,
        ]);

        broadcast(new AntrianStatusUpdate($antrian));

        return response()->json([
            'message' => 'Pemeriksaan kesehatan selesai. Santri diarahkan ke meja pembayaran.',
        ]);
    }
}
