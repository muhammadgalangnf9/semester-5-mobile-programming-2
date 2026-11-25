<?php

namespace App\Http\Controllers\Api\WargaBiasa;

use App\Http\Controllers\Controller;
use App\Models\Pengumuman;
use Illuminate\Http\Request;

class PengumumanApiController extends Controller
{
    /**
     * Menampilkan daftar pengumuman yang sudah diterbitkan.
     */
    public function index(Request $request)
    {
        $pengumuman = Pengumuman::with(['pengguna:id,nama', 'disetujuiOleh:id,nama'])
            ->where('status', 'terbit')
            ->whereNotNull('diterbitkan_pada')
            ->orderByDesc('diterbitkan_pada')
            ->paginate(10);

        return response()->json([
            'berhasil' => true,
            'pesan' => 'Daftar pengumuman berhasil diambil.',
            'data' => $pengumuman->items(),
            'meta' => [
                'current_page' => $pengumuman->currentPage(),
                'last_page' => $pengumuman->lastPage(),
                'per_page' => $pengumuman->perPage(),
                'total' => $pengumuman->total(),
            ],
        ]);
    }

    /**
     * Menampilkan detail pengumuman.
     */
    public function show($id)
    {
        $pengumuman = Pengumuman::with(['pengguna:id,nama', 'disetujuiOleh:id,nama'])
            ->where('status', 'terbit')
            ->whereNotNull('diterbitkan_pada')
            ->find($id);

        if (!$pengumuman) {
            return response()->json([
                'berhasil' => false,
                'pesan' => 'Pengumuman tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'berhasil' => true,
            'pesan' => 'Detail pengumuman berhasil diambil.',
            'data' => $pengumuman,
        ]);
    }
}
