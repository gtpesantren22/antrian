<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Antrian;
use App\Models\AntrianLog;
use App\Models\PemanggilanLock;
use App\Services\AntrianService;
use App\Events\AntrianDipanggil;
use App\Events\AntrianStatusUpdate;

class LayananController extends Controller
{
    public function __construct(private AntrianService $antrianService) {}

    public function index(Request $request)
    {
        $meja = $request->sesi_meja;

        // Antrian yang sedang ditangani meja ini (kalau ada)
        $antrianAktif = Antrian::hariIni()
            ->where('meja_layanan_id', $meja->id)
            ->whereIn('status', ['dipanggil_layanan', 'diproses_layanan'])
            ->with('santri')
            ->first();

        // Jumlah antrian menunggu (untuk info di UI)
        $totalMenunggu = Antrian::hariIni()->menunggu()->count();

        // Lock aktif? (untuk disable tombol panggil saat meja lain sedang memanggil)
        $lockAktif = $this->antrianService->isLockAktif();

        return view('layanan.index', compact('antrianAktif', 'totalMenunggu', 'lockAktif', 'meja'));
    }

    // Ambil nomor antrian terkecil yang belum dilayani
    public function ambilAntrian(Request $request)
    {
        $meja = $request->sesi_meja;

        // Cek apakah meja ini sudah punya antrian aktif
        $sudahAda = Antrian::hariIni()
            ->where('meja_layanan_id', $meja->id)
            ->whereIn('status', ['dipanggil_layanan', 'diproses_layanan'])
            ->exists();

        if ($sudahAda) {
            return response()->json([
                'message' => 'Selesaikan antrian yang sedang berjalan terlebih dahulu.',
            ], 422);
        }

        $antrian = $this->antrianService->ambilAntrianLayanan();

        if (!$antrian) {
            return response()->json([
                'message' => 'Tidak ada antrian yang menunggu.',
            ], 404);
        }

        $antrian->update([
            'status'                    => 'dipanggil_layanan',
            'meja_layanan_id'           => $meja->id,
            'dipanggil_oleh_layanan_id' => $request->sesi_user->id,
            'waktu_dipanggil_layanan'   => now(),
        ]);

        AntrianLog::create([
            'antrian_id'     => $antrian->id,
            'status_sebelum' => 'menunggu',
            'status_sesudah' => 'dipanggil_layanan',
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

    // Mulai proses administrasi
    public function mulaiProses(Request $request, Antrian $antrian)
    {
        if ($antrian->status !== 'dipanggil_layanan') {
            return response()->json(['message' => 'Status antrian tidak valid.'], 422);
        }

        $antrian->update([
            'status'             => 'diproses_layanan',
            'waktu_mulai_layanan' => now(),
        ]);

        AntrianLog::create([
            'antrian_id'     => $antrian->id,
            'status_sebelum' => 'dipanggil_layanan',
            'status_sesudah' => 'diproses_layanan',
            'meja_id'        => $request->sesi_meja->id,
            'user_id'        => $request->sesi_user->id,
        ]);

        broadcast(new AntrianStatusUpdate($antrian));

        return response()->json(['message' => 'Proses administrasi dimulai.']);
    }

    // Selesai administrasi → masuk pool pembayaran
    public function selesai(Request $request, Antrian $antrian)
    {
        if ($antrian->status !== 'diproses_layanan') {
            return response()->json(['message' => 'Status antrian tidak valid.'], 422);
        }

        $antrian->update([
            'status'               => 'menunggu_pembayaran',
            'waktu_selesai_layanan' => now(),
        ]);

        AntrianLog::create([
            'antrian_id'     => $antrian->id,
            'status_sebelum' => 'diproses_layanan',
            'status_sesudah' => 'menunggu_pembayaran',
            'meja_id'        => $request->sesi_meja->id,
            'user_id'        => $request->sesi_user->id,
        ]);

        broadcast(new AntrianStatusUpdate($antrian));

        return response()->json([
            'message' => 'Administrasi selesai. Santri diarahkan ke meja pembayaran.',
        ]);
    }
}
