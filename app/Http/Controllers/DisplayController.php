<?php

namespace App\Http\Controllers;

use App\Models\Antrian;

class DisplayController extends Controller
{
    // Halaman layar TV — tidak butuh sesi
    public function index()
    {
        // Antrian yang baru saja dipanggil (untuk ditampilkan di layar)
        $terakhirDipanggil = Antrian::hariIni()
            ->whereIn('status', ['dipanggil_layanan', 'diproses_layanan', 'dipanggil_kesehatan', 'diproses_kesehatan', 'dipanggil_pembayaran'])
            ->with(['santri', 'mejaLayanan', 'mejaKesehatan', 'mejaPembayaran'])
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        return view('display.index', compact('terakhirDipanggil'));
    }
}
