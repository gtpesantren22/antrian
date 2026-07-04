<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Meja;
use App\Services\AntrianService;

class SesiController extends Controller
{
    public function __construct(private AntrianService $antrianService) {}

    // Halaman pilih meja
    public function index()
    {
        $mejas = Meja::aktif()->get()->map(function ($meja) {
            return [
                'id'              => $meja->id,
                'nama_meja'       => $meja->nama_meja,
                'tipe'            => $meja->tipe,
                'sedang_ditempati' => $meja->isSedangDitempati(),
            ];
        });

        return view('sesi.pilih-meja', compact('mejas'));
    }

    // Proses pilih meja + input PIN
    public function login(Request $request)
    {
        $request->validate([
            'meja_id' => 'required|exists:meja,id',
            'pin'     => 'required|digits:4',
        ]);

        $token = $this->antrianService->loginMeja(
            $request->meja_id,
            $request->pin
        );

        if (!$token) {
            return back()->with('error', 'PIN salah. Silakan coba lagi.');
        }

        // Simpan token di cookie (httpOnly, 12 jam)
        $meja = Meja::find($request->meja_id);
        $redirectTo = $this->redirectByTipe($meja->tipe);

        return redirect($redirectTo)
            ->withCookie(cookie('sesi_token', $token, 720)); // 720 menit = 12 jam
    }

    // Logout
    public function logout(Request $request)
    {
        $token = $request->cookie('sesi_token');

        if ($token) {
            $this->antrianService->logoutMeja($token);
        }

        return redirect()->route('pilih.meja')
            ->withoutCookie('sesi_token')
            ->with('success', 'Sesi berhasil diakhiri.');
    }

    private function redirectByTipe(string $tipe): string
    {
        return match ($tipe) {
            'resepsionis' => route('resepsionis.index'),
            'layanan'     => route('layanan.index'),
            'kesehatan'   => route('kesehatan.index'),
            'pembayaran'  => route('pembayaran.index'),
            default       => route('pilih.meja'),
        };
    }
}
