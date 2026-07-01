<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\MejaSesi;
use Symfony\Component\HttpFoundation\Response;

class ValidasiSesi
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->cookie('sesi_token');

        if (!$token) {
            return $this->unauthorized($request);
        }

        $sesi = MejaSesi::with(['meja', 'user'])
            ->where('session_token', $token)
            ->where('expired_at', '>', now())
            ->first();

        if (!$sesi) {
            return $this->unauthorized($request);
        }

        // Perpanjang sesi (sliding session)
        $sesi->update([
            'expired_at'       => now()->addHours(12),
            'last_activity_at' => now(),
        ]);

        // Inject ke request supaya semua controller bisa akses langsung
        $request->merge([
            'sesi'       => $sesi,
            'sesi_meja'  => $sesi->meja,
            'sesi_user'  => $sesi->user,
        ]);

        // Share ke semua view Blade
        view()->share('sesiMeja', $sesi->meja);
        view()->share('sesiUser', $sesi->user);

        return $next($request);
    }

    private function unauthorized(Request $request): Response
    {
        // Kalau request dari API/AJAX → return JSON
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Sesi tidak valid atau sudah berakhir.',
            ], 401);
        }

        // Kalau request biasa → redirect ke halaman pilih meja
        return redirect()->route('pilih.meja')
            ->with('error', 'Silakan pilih meja dan masukkan PIN terlebih dahulu.');
    }
}
