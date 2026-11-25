<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use App\Models\Role;
use App\Models\LogAktivitas;
use App\Models\Pengumuman;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $pengguna = Auth::user();

        // Menentukan sapaan berdasarkan waktu.
        $jam = now()->format('H');

        if ($jam >= 5 && $jam < 11) {
            $sapaan = 'Selamat pagi';
        } elseif ($jam >= 11 && $jam < 15) {
            $sapaan = 'Selamat siang';
        } elseif ($jam >= 15 && $jam < 18) {
            $sapaan = 'Selamat sore';
        } else {
            $sapaan = 'Selamat malam';
        }

        // ID role Warga dan Warga Biasa.
        $roleWarga = [2, 3];

        // Jumlah warga aktif.
        $totalWargaAktif = Pengguna::whereIn('role_id', $roleWarga)
            ->where('status', 'aktif')
            ->count();

        // Jumlah warga nonaktif.
        $totalWargaNonaktif = Pengguna::whereIn('role_id', $roleWarga)
            ->where('status', 'nonaktif')
            ->count();

        // Jumlah role yang terdaftar.
        $totalRole = Role::count();

        // Mengambil 5 pengumuman terbaru yang sudah diterbitkan.
        $pengumumanTerbaru = Pengumuman::with('pengguna')
            ->where('status', 'terbit')
            ->orderByDesc('diterbitkan_pada')
            ->take(5)
            ->get();

        // Mengambil 8 aktivitas terbaru.
        $aktivitasTerbaru = LogAktivitas::with('pengguna')
            ->latest()
            ->take(8)
            ->get();

        return view('dashboard.index', compact(
            'pengguna',
            'sapaan',
            'totalWargaAktif',
            'totalWargaNonaktif',
            'totalRole',
            'pengumumanTerbaru',
            'aktivitasTerbaru'
        ));
    }

    public function pengumuman()
    {
        $pengguna = Auth::user();

        abort_unless(
            in_array((int) $pengguna->role_id, [1, 2, 3], true),
            403
        );

        $pengumuman = Pengumuman::with('pengguna')
            ->where('status', 'terbit')
            ->orderByDesc('diterbitkan_pada')
            ->paginate(10);

        return view('dashboard.show', compact(
            'pengguna',
            'pengumuman'
        ));
    }
}
