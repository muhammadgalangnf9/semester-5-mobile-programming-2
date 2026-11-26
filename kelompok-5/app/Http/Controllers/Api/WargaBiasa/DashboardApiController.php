<?php

namespace App\Http\Controllers\Api\WargaBiasa;

use App\Http\Controllers\Controller;
use App\Models\JadwalRonda;
use App\Models\Kegiatan;
use App\Models\Pengumuman;
use Illuminate\Http\Request;

class DashboardApiController extends Controller
{
    /**
     * Menampilkan data dashboard untuk Warga Biasa.
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        // Menentukan sapaan
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

        // Pengumuman terbaru yang terbit
        $pengumumanTerbaru = Pengumuman::with('pengguna:id,nama')
            ->where('status', 'terbit')
            ->orderByDesc('diterbitkan_pada')
            ->get();

        // Jadwal aktif yang mengikutsertakan warga yang login.
        $jadwalRondaSaya = JadwalRonda::where('status', 'aktif')
            ->orderBy('waktu_mulai')
            ->get()
            ->filter(fn (JadwalRonda $jadwal) => in_array(
                (int) $userId,
                array_map('intval', $jadwal->peserta ?? []),
                true
            ))
            ->values();

        // Kegiatan yang sudah disetujui / aktif
        $kegiatanTerbaru = Kegiatan::whereIn('status', ['disetujui', 'selesai'])
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'berhasil' => true,
            'pesan' => 'Data dashboard Warga Biasa berhasil diambil.',
            'data' => [
                'sapaan' => $sapaan,
                'pengumuman_terbaru' => $pengumumanTerbaru,
                'jadwal_ronda_saya' => $jadwalRondaSaya,
                'kegiatan_terbaru' => $kegiatanTerbaru,
            ],
        ]);
    }
}
