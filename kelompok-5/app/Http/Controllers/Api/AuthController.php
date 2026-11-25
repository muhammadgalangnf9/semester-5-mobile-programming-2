<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use App\Models\PercobaanLogin;
use App\Models\TokenAkses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Batas gagal login sebelum dikunci.
     */
    private const MAKS_GAGAL = 5;

    /**
     * Durasi kunci dalam detik.
     */
    private const DURASI_KUNCI = 60;

    /**
     * Login pengguna.
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:50',
            'password' => 'required|string',
        ], [
            'username.required' => 'Username wajib diisi.',
            'username.max' => 'Username maksimal 50 karakter.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $username = $request->input('username');
        $password = $request->input('password');
        $alamatIp = $request->ip();

        // 1. Cek / buat record percobaan login
        $percobaan = PercobaanLogin::firstOrCreate(
            [
                'username' => $username,
                'alamat_ip' => $alamatIp,
            ],
            ['jumlah_gagal' => 0]
        );

        // 2. Cek apakah masih dikunci
        if ($percobaan->sedangDikunci()) {
            $sisaDetik = $percobaan->sisaDetikKunci();

            return response()->json([
                'berhasil' => false,
                'pesan' => "Terlalu banyak percobaan login. Silakan coba kembali dalam {$sisaDetik} detik.",
                'sisa_detik' => $sisaDetik,
            ], 429);
        }

        // 3. Reset counter jika masa kunci sudah berakhir
        if ($percobaan->dikunci_sampai !== null) {
            $percobaan->jumlah_gagal = 0;
            $percobaan->dikunci_sampai = null;
            $percobaan->save();
        }

        // 4. Cek username dan password
        $pengguna = Pengguna::where('username', $username)->first();

        if (! $pengguna || ! Hash::check($password, $pengguna->password)) {
            $percobaan->jumlah_gagal++;

            if ($percobaan->jumlah_gagal >= self::MAKS_GAGAL) {
                $percobaan->jumlah_gagal = self::MAKS_GAGAL;
                $percobaan->dikunci_sampai = now()->addSeconds(self::DURASI_KUNCI);
                $percobaan->save();

                return response()->json([
                    'berhasil' => false,
                    'pesan' => 'Kamu telah gagal login ' . self::MAKS_GAGAL
                        . ' kali. Login dikunci selama '
                        . self::DURASI_KUNCI . ' detik.',
                    'sisa_detik' => self::DURASI_KUNCI,
                ], 429);
            }

            $percobaan->save();

            return response()->json([
                'berhasil' => false,
                'pesan' => 'Username atau password salah.',
                'sisa_percobaan' => self::MAKS_GAGAL - $percobaan->jumlah_gagal,
            ], 401);
        }

        // 5. Pastikan akun masih aktif
        if (! $pengguna->akunAktif()) {
            return response()->json([
                'berhasil' => false,
                'pesan' => 'Akun kamu sudah dinonaktifkan. Silakan hubungi Ketua RT.',
            ], 403);
        }

        // 6. Login berhasil: reset counter
        $percobaan->jumlah_gagal = 0;
        $percobaan->dikunci_sampai = null;
        $percobaan->save();

        // 7. Cabut semua token lama
        TokenAkses::where('pengguna_id', $pengguna->id)->delete();

        // Buat token baru
        $token = bin2hex(random_bytes(32));

        TokenAkses::create([
            'pengguna_id' => $pengguna->id,
            'token_hash' => hash('sha256', $token),
            'berlaku_sampai' => null,
        ]);

        // 8. Response login
        return response()->json([
            'berhasil' => true,
            'pesan' => 'Login berhasil.',
            'data' => [
                'id' => $pengguna->id,
                'username' => $pengguna->username,
                'role' => $pengguna->role?->nama_role,
                'akses' => $pengguna->daftarAkses(),
            ],
            'token' => $token,
            'jenis_token' => 'Bearer',
            'berlaku_sampai' => null,
        ], 200);
    }

    /**
     * Logout: hapus token yang sedang digunakan.
     */
    public function logout(Request $request)
    {
        $tokenAkses = $request->attributes->get('token_akses');

        if (! $tokenAkses) {
            return response()->json([
                'berhasil' => false,
                'pesan' => 'Token autentikasi tidak ditemukan.',
            ], 401);
        }

        $tokenAkses->delete();

        return response()->json([
            'berhasil' => true,
            'pesan' => 'Logout berhasil.',
        ], 200);
    }

    /**
     * Endpoint untuk Flutter memeriksa token dan mengambil profil pengguna.
     */
    public function me(Request $request)
    {
        /** @var Pengguna $pengguna */
        $pengguna = $request->attributes->get('pengguna');

        return response()->json([
            'berhasil' => true,
            'data' => [
                'id' => $pengguna->id,
                'username' => $pengguna->username,
                'role' => $pengguna->role?->nama_role,
                'akses' => $pengguna->daftarAkses(),
            ],
        ], 200);
    }
}
