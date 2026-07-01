<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Santri;
use App\Models\Antrian;
use App\Models\AntrianLog;
use Illuminate\Support\Facades\Http;
use App\Services\AntrianService;
use App\Events\AntrianBaru;

class ResepsionisController extends Controller
{
    public function __construct(private AntrianService $antrianService) {}

    public function index(Request $request)
    {
        // Antrian hari ini untuk ditampilkan di sidebar
        $antriansHariIni = Antrian::hariIni()
            ->with('santri')
            ->orderBy('no_antrian')
            ->get();

        return view('resepsionis.index', compact('antriansHariIni'));
    }

    // Cari santri — dipanggil via AJAX saat petugas mengetik nama
    public function cariSantri(Request $request)
    {
        $request->validate([
            'keyword' => 'required|min:2',
        ]);

        $token = config('services.ppdwk.token');

        try {
            // Panggil API external dengan parameter keyword
            $response = Http::withToken($token)
                ->timeout(10)
                ->get('https://data.ppdwk.com/api/datatables', [
                    'data'       => 'pendaftar',
                    'page'       => 1,
                    'per_page'   => 15,
                    'q'          => $request->keyword,
                    'sortby'     => 'created_at',
                    'sortbydesc' => 'DESC',
                    'status'     => 1,
                ]);

            if ($response->successful()) {
                $body = $response->json();
                $items = $body['data']['data'] ?? $body['data'] ?? [];

                foreach ($items as $item) {
                    $nama = $item['nama'] ?? $item['name'] ?? null;
                    // Prioritaskan ID dari API sebagai no_induk, baru kemudian alternatif lainnya
                    $noInduk = $item['peserta_didik_id'] ?? $item['no_induk'] ?? $item['nis'] ?? $item['nisn'] ?? $item['no_pendaftaran'] ?? $item['nomor_pendaftaran'] ?? $item['no_induk_santri'] ?? null;

                    if (!$nama) continue;

                    if (!$noInduk) {
                        $noInduk = uniqid('SNT-');
                    }

                    $namaAyah   = $item['nama_ayah'] ?? $item['ayah_nama'] ?? $item['nama_ortu'] ?? null;
                    $asalDaerah = $item['asal_daerah'] ?? $item['alamat'] ?? $item['kabupaten'] ?? $item['asal'] ?? null;
                    $noHp       = $item['no_hp'] ?? $item['telepon'] ?? $item['hp'] ?? $item['no_telepon'] ?? null;

                    // Simpan/update ke database lokal agar punya ID lokal untuk antrian
                    Santri::updateOrCreate(
                        ['no_induk' => $noInduk],
                        [
                            'nama'        => $nama,
                            'nama_ayah'   => $namaAyah,
                            'asal_daerah' => $asalDaerah,
                            'no_hp'       => $noHp,
                        ]
                    );
                }
            }
        } catch (\Exception $e) {
            // Jika API down, catat log dan fallback ke pencarian lokal
            \Illuminate\Support\Facades\Log::warning('External API failed, fallback to local: ' . $e->getMessage());
        }

        // Cari di DB lokal (yang sudah tersinkronisasi)
        $santris = Santri::cari($request->keyword)
            ->with('antrianHariIni')
            ->limit(10)
            ->get()
            ->map(function ($santri) {
                return [
                    'id'            => $santri->id,
                    'nama'          => $santri->nama,
                    'no_induk'      => $santri->no_induk,
                    'asal_daerah'   => $santri->asal_daerah,
                    'sudah_antrian' => $santri->antrianHariIni()->exists(),
                ];
            });

        return response()->json($santris);
    }

    // Sinkronisasi data santri secara massal dari API external
    public function syncSantri(Request $request)
    {
        $token = config('services.ppdwk.token');

        try {
            $response = Http::withToken($token)
                ->timeout(30)
                ->get('https://data.ppdwk.com/api/datatables', [
                    'data'       => 'pendaftar',
                    'page'       => 1,
                    'per_page'   => 100, // Ambil 100 data
                    'q'          => '',
                    'sortby'     => 'created_at',
                    'sortbydesc' => 'DESC',
                    'status'     => 1,
                ]);

            if (!$response->successful()) {
                return response()->json([
                    'message' => 'Gagal menghubungi API external (Status: ' . $response->status() . ').',
                ], 502);
            }

            $body = $response->json();
            $items = $body['data']['data'] ?? $body['data'] ?? [];

            if (empty($items)) {
                return response()->json([
                    'message' => 'Tidak ada data santri ditemukan di API external.',
                ]);
            }

            $syncedCount = 0;
            foreach ($items as $item) {
                $nama = $item['nama'] ?? $item['name'] ?? null;
                // Prioritaskan ID dari API sebagai no_induk, baru kemudian alternatif lainnya
                $noInduk = $item['id'] ?? $item['no_induk'] ?? $item['nis'] ?? $item['nisn'] ?? $item['no_pendaftaran'] ?? $item['nomor_pendaftaran'] ?? $item['no_induk_santri'] ?? null;

                if (!$nama) continue;

                if (!$noInduk) {
                    $noInduk = uniqid('SNT-');
                }

                $namaAyah   = $item['nama_ayah'] ?? $item['ayah_nama'] ?? $item['nama_ortu'] ?? null;
                $asalDaerah = $item['asal_daerah'] ?? $item['alamat'] ?? $item['kabupaten'] ?? $item['asal'] ?? null;
                $noHp       = $item['no_hp'] ?? $item['telepon'] ?? $item['hp'] ?? $item['no_telepon'] ?? null;

                Santri::updateOrCreate(
                    ['no_induk' => $noInduk],
                    [
                        'nama'        => $nama,
                        'nama_ayah'   => $namaAyah,
                        'asal_daerah' => $asalDaerah,
                        'no_hp'       => $noHp,
                    ]
                );
                $syncedCount++;
            }

            return response()->json([
                'message' => "Sinkronisasi berhasil. {$syncedCount} data santri telah diperbarui.",
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('API Sync Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Gagal melakukan sinkronisasi: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Tambah santri ke antrian + return data untuk cetak struk
    public function tambahAntrian(Request $request, Santri $santri)
    {
        // Cegah santri masuk antrian dua kali di hari yang sama
        if ($santri->antrianHariIni()->exists()) {
            return response()->json([
                'message' => "{$santri->nama} sudah memiliki nomor antrian hari ini.",
            ], 422);
        }

        $noAntrian = $this->antrianService->generateNoAntrian();

        $antrian = Antrian::create([
            'no_antrian'   => $noAntrian,
            'santri_id'    => $santri->id,
            'status'       => 'menunggu',
            'waktu_daftar' => now(),
        ]);

        // Update status santri
        $santri->update(['status' => 'hadir']);

        // Catat log
        AntrianLog::create([
            'antrian_id'    => $antrian->id,
            'status_sebelum' => null,
            'status_sesudah' => 'menunggu',
            'meja_id'       => $request->sesi_meja->id,
            'user_id'       => $request->sesi_user->id,
        ]);

        // Broadcast ke semua meja layanan (update list antrian)
        broadcast(new AntrianBaru($antrian->load('santri')));

        // Return data untuk QZ Tray / cetak struk
        return response()->json([
            'message'  => 'Antrian berhasil dibuat.',
            'antrian'  => [
                'id'         => $antrian->id,
                'no_antrian' => $antrian->no_antrian,
                'nama'       => $santri->nama,
                'no_induk'   => $santri->no_induk,
                'waktu'      => $antrian->waktu_daftar->format('H:i'),
                'tanggal'    => $antrian->tanggal->format('d/m/Y'),
            ],
        ]);
    }

    // Batalkan antrian
    public function batalAntrian(Request $request, Antrian $antrian)
    {
        if (!in_array($antrian->status, ['menunggu'])) {
            return response()->json([
                'message' => 'Antrian tidak bisa dibatalkan karena sudah diproses.',
            ], 422);
        }

        $statusLama = $antrian->status;

        $antrian->update(['status' => 'batal']);

        AntrianLog::create([
            'antrian_id'     => $antrian->id,
            'status_sebelum' => $statusLama,
            'status_sesudah' => 'batal',
            'meja_id'        => $request->sesi_meja->id,
            'user_id'        => $request->sesi_user->id,
            'keterangan'     => 'Dibatalkan oleh resepsionis',
        ]);

        return response()->json(['message' => 'Antrian berhasil dibatalkan.']);
    }
}
