<?php

namespace App\Http\Controllers\Api\KetuaRT;

use App\Http\Controllers\Controller;
use App\Models\Kegiatan;
use App\Models\LogAktivitas;
use App\Models\Pengguna;
use App\Models\Pengumuman;
use App\Models\Role;
use Illuminate\Http\Request;

class DashboardApiController extends Controller
{
    /**
     * Menampilkan data statistik dashboard untuk Ketua RT.
     */
    public function index(Request $request)
    {
        $roleWarga = [2, 3];

        $totalWargaAktif = Pengguna::whereIn('role_id', $roleWarga)
            ->where('status', 'aktif')
            ->count();

        $totalWargaNonaktif = Pengguna::whereIn('role_id', $roleWarga)
            ->where('status', 'nonaktif')
            ->count();

        $totalRole = Role::count();

        $totalKegiatanMenunggu = Kegiatan::where('status', 'menunggu_persetujuan')->count();

        $pengumumanTerbaru = Pengumuman::with('pengguna:id,nama')
            ->where('status', 'terbit')
            ->orderByDesc('diterbitkan_pada')
            ->take(5)
            ->get();

        $aktivitasTerbaru = LogAktivitas::with('pengguna:id,nama')
            ->latest()
            ->take(8)
            ->get();

        return response()->json([
            'berhasil' => true,
            'pesan' => 'Data dashboard Ketua RT berhasil diambil.',
            'data' => [
                'total_warga_aktif' => $totalWargaAktif,
                'total_warga_nonaktif' => $totalWargaNonaktif,
                'total_role' => $totalRole,
                'total_kegiatan_menunggu_persetujuan' => $totalKegiatanMenunggu,
                'pengumuman_terbaru' => $pengumumanTerbaru,
                'aktivitas_terbaru' => $aktivitasTerbaru,
            ],
        ]);
    }
}
