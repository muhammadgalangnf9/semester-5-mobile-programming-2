<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        $key = 'login.' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            return view('auth.login')
                ->with('sisa_detik', $seconds);
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:50',
            'password' => 'required|string',
        ]);

        $key = 'login.' . $request->ip();

        // Cek apakah pengguna sedang diblokir
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            return back()
                ->withInput($request->only('username'))
                ->with('sisa_detik', $seconds)
                ->withErrors([
                    'username' => "Anda diblokir. Silakan coba lagi dalam {$seconds} detik."
                ]);
        }

        // Cari akun berdasarkan username
        $pengguna = Pengguna::where(
            'username',
            $request->username
        )->first();

        // Tolak login jika akun nonaktif
        if ($pengguna && $pengguna->status !== 'aktif') {
            return back()
                ->withInput($request->only('username'))
                ->withErrors([
                    'username' => 'Akun Anda sedang nonaktif. Silakan hubungi Ketua RT.'
                ]);
        }

        // Proses autentikasi
        if (Auth::attempt($request->only('username', 'password'))) {
            RateLimiter::clear($key);

            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        // Tambahkan hitungan percobaan gagal
        RateLimiter::hit($key, 60);

        // Periksa apakah sudah mencapai batas percobaan
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            return back()
                ->withInput($request->only('username'))
                ->with('sisa_detik', $seconds)
                ->withErrors([
                    'username' => "Anda diblokir. Silakan coba lagi dalam {$seconds} detik."
                ]);
        }

        // Informasi sisa percobaan
        $sisa = 5 - RateLimiter::attempts($key);

        return back()
            ->withInput($request->only('username'))
            ->withErrors([
                'username' => "Username atau password salah. Sisa percobaan: {$sisa}."
            ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
