<?php

namespace App\Http\Middleware;

use App\Models\TokenAkses;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('Authorization');

        // Validasi format Bearer Token
        if (! $header || ! preg_match('/^Bearer\s+(\S+)$/i', $header, $cocok)) {
            return response()->json([
                'berhasil' => false,
                'pesan' => 'Token autentikasi tidak ditemukan.',
            ], 401);
        }

        $token = $cocok[1];

        // Cari token berdasarkan hash
        $tokenAkses = TokenAkses::with('pengguna')
            ->where('token_hash', hash('sha256', $token))
            ->first();

        if (! $tokenAkses) {
            return response()->json([
                'berhasil' => false,
                'pesan' => 'Token tidak valid atau sudah dicabut.',
            ], 401);
        }

        // Periksa masa berlaku token
        if ($tokenAkses->sudahKedaluwarsa()) {
            $tokenAkses->delete();

            return response()->json([
                'berhasil' => false,
                'pesan' => 'Token sudah kedaluwarsa.',
            ], 401);
        }

        $pengguna = $tokenAkses->pengguna;

        // Pastikan pengguna masih tersedia
        if (! $pengguna) {
            $tokenAkses->delete();

            return response()->json([
                'berhasil' => false,
                'pesan' => 'Akun pengguna tidak ditemukan.',
            ], 401);
        }

        // Pastikan akun masih aktif
        if (! $pengguna->akunAktif()) {
            $tokenAkses->delete();

            return response()->json([
                'berhasil' => false,
                'pesan' => 'Akun kamu sudah dinonaktifkan.',
            ], 403);
        }

        // Simpan data autentikasi ke request
        $request->attributes->set('token_akses', $tokenAkses);
        $request->attributes->set('pengguna', $pengguna);

        $request->setUserResolver(fn () => $pengguna);

        return $next($request);
    }
}
