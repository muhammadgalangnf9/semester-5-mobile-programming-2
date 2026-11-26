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
        $filter = $request->validate([
            'nama_pengumuman' => ['nullable', 'string', 'max:150'],
            'tanggal_dibuat' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $pengumuman = Pengumuman::with([
            'pengguna:id,nama',
            'pengubah:id,nama',
            'disetujuiOleh:id,nama',
        ])
            ->where('status', 'terbit')
            ->whereNotNull('diterbitkan_pada')
            ->when(
                $filter['nama_pengumuman'] ?? null,
                fn ($query, $nama) => $query->where('judul', 'like', "%{$nama}%")
            )
            ->when(
                $filter['tanggal_dibuat'] ?? null,
                fn ($query, $tanggal) => $query->whereDate('created_at', $tanggal)
            )
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
        $pengumuman = Pengumuman::with([
            'pengguna:id,nama',
            'pengubah:id,nama',
            'disetujuiOleh:id,nama',
        ])
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
