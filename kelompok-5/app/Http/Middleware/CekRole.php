<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CekRole
{
    public function handle(
        Request $request,
        Closure $next,
        string ...$roles
    ): Response {
        $pengguna = $request->user();

        if (!$pengguna) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan login terlebih dahulu.',
            ], 401);
        }

        $namaRole = $pengguna->role?->nama_role;

        if (!in_array($namaRole, $roles, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk mengakses fitur ini.',
            ], 403);
        }

        return $next($request);
    }
}
