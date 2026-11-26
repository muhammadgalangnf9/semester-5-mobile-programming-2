<?php

namespace App\Http\Controllers\Api\WargaPJKegiatandanJadwalRonda;

use App\Http\Controllers\Controller;
use App\Models\JadwalRonda;
use App\Models\Kegiatan;
use App\Models\Pengumuman;
use Illuminate\Http\Request;

class DashboardApiController extends Controller
{
    /**
     * Menampilkan data dashboard untuk Warga PJ Kegiatan dan Jadwal Ronda.
     */
    public function index(Request $request)
    {
        $roleId = $request->user()->role_id;

        // Jam dan sapaan
        $jam = (int) now()->format('H');
        if ($jam >= 5 && $jam < 11) {
            $sapaan = 'Selamat pagi';
        } elseif ($jam >= 11 && $jam < 15) {
            $sapaan = 'Selamat siang';
        } elseif ($jam >= 15 && $jam < 18) {
            $sapaan = 'Selamat sore';
        } else {
            $sapaan = 'Selamat malam';
        }

        $kegiatanSatuRole = fn () => Kegiatan::whereHas(
            'pengguna',
            fn ($query) => $query->where('role_id', $roleId)
        );
        $pengumumanSatuRole = fn () => Pengumuman::whereHas(
            'pengguna',
            fn ($query) => $query->where('role_id', $roleId)
        );
        $jadwalRondaSatuRole = fn () => JadwalRonda::whereHas(
            'pengguna',
            fn ($query) => $query->where('role_id', $roleId)
        );

        $totalKegiatanSaya = $kegiatanSatuRole()->count();
        $totalPengumumanSaya = $pengumumanSatuRole()->count();
        $totalJadwalRondaSaya = $jadwalRondaSatuRole()->count();

        $kegiatanSaya = $kegiatanSatuRole()
            ->latest()
            ->take(3)
            ->get();

        $pengumumanSaya = $pengumumanSatuRole()
            ->latest()
            ->take(3)
            ->get();

        $jadwalRondaSaya = $jadwalRondaSatuRole()
            ->latest()
            ->take(3)
            ->get();

        return response()->json([
            'berhasil' => true,
            'pesan' => 'Data dashboard Warga PJ berhasil diambil.',
            'data' => [
                'sapaan' => $sapaan,
                'total_kegiatan_saya' => $totalKegiatanSaya,
                'total_pengumuman_saya' => $totalPengumumanSaya,
                'total_jadwal_ronda_saya' => $totalJadwalRondaSaya,
                'kegiatan_saya' => $kegiatanSaya,
                'pengumuman_saya' => $pengumumanSaya,
                'jadwal_ronda_saya' => $jadwalRondaSaya,
            ],
        ]);
    }
}
