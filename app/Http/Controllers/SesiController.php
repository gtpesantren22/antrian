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

    // Reset meja/sesi aktif oleh Admin
    public function resetMeja(Request $request)
    {
        $request->validate([
            'meja_id' => 'required|exists:meja,id',
            'pin'     => 'required|digits:4',
        ]);

        // Cari user admin (meja_id is null dan nama 'Admin')
        $admin = \App\Models\User::whereNull('meja_id')
            ->where('nama', 'Admin')
            ->where('is_active', true)
            ->first();

        if (!$admin || !\Hash::check($request->pin, $admin->pin)) {
            return response()->json([
                'message' => 'PIN Admin tidak valid.',
            ], 422);
        }

        $meja = Meja::findOrFail($request->meja_id);

        if ($meja->isSedangDitempati()) {
            // Terminasi sesi aktif (set expired_at ke waktu sekarang)
            $meja->sesiAktif()->update(['expired_at' => now()]);
            
            return response()->json([
                'message' => "Sesi {$meja->nama_meja} berhasil di-reset.",
            ]);
        }

        return response()->json([
            'message' => "Meja {$meja->nama_meja} tidak sedang ditempati.",
        ]);
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
