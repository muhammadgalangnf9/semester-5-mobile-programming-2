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
        $userId = $request->user()->id;

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

        $totalKegiatanSaya = Kegiatan::where('dibuat_oleh', $userId)->count();
        $totalPengumumanSaya = Pengumuman::where('pengguna_id', $userId)->count();
        $totalJadwalRondaSaya = JadwalRonda::where('dibuat_oleh', $userId)->count();

        $kegiatanSaya = Kegiatan::where('dibuat_oleh', $userId)
            ->latest()
            ->take(3)
            ->get();

        $pengumumanSaya = Pengumuman::where('pengguna_id', $userId)
            ->latest()
            ->take(3)
            ->get();

        $jadwalRondaSaya = JadwalRonda::where('dibuat_oleh', $userId)
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
