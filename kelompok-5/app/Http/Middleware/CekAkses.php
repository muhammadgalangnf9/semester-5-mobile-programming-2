<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CekAkses
{
    /**
     * Memeriksa hak akses pengguna berdasarkan role.
     */
    public function handle(
        Request $request,
        Closure $next,
        string $akses
    ): Response {
        // Mengambil pengguna dari AuthToken.
        $pengguna = $request->attributes->get('pengguna');

        // Memastikan pengguna sudah terautentikasi.
        if (!$pengguna) {
            return response()->json([
                'berhasil' => false,
                'pesan' => 'Pengguna tidak terautentikasi.',
            ], 401);
        }

        // Ketua RT memiliki akses penuh terhadap fitur.
        if ($pengguna->isKetuaRt()) {
            return $next($request);
        }

        // Akses fitur petugas diberikan kepada role Warga.
        if ((int) $pengguna->role_id !== 2) {
            return response()->json([
                'berhasil' => false,
                'pesan' => 'Anda tidak memiliki akses untuk fitur ini.',
            ], 403);
        }

        return $next($request);
    }
}
