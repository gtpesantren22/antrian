<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidasiTipeMeja
{
    /**
     * Cara pakai di route:
     * ->middleware('tipe.meja:layanan')
     * ->middleware('tipe.meja:resepsionis,admin')  ← bisa lebih dari satu tipe
     */
    public function handle(Request $request, Closure $next, string ...$tipes): Response
    {
        $mejaTipe = $request->sesi_meja?->tipe;

        if (!$mejaTipe || !in_array($mejaTipe, $tipes)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Meja Anda tidak memiliki akses ke fitur ini.',
                ], 403);
            }

            return redirect()->back()
                ->with('error', 'Halaman ini tidak tersedia untuk meja Anda.');
        }

        return $next($request);
    }
}
