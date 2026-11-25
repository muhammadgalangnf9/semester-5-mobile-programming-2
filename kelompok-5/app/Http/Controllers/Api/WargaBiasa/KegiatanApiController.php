<?php

namespace App\Http\Controllers\Api\WargaBiasa;

use App\Http\Controllers\Controller;
use App\Models\Kegiatan;
use Illuminate\Http\Request;

class KegiatanApiController extends Controller
{
    /**
     * Menampilkan kegiatan yang sudah disetujui atau selesai.
     */
    public function index(Request $request)
    {
        $filter = $request->validate([
            'nama_kegiatan' => ['nullable', 'string', 'max:150'],
            'tanggal_dibuat' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $kegiatan = Kegiatan::whereIn('status', [
                'disetujui',
                'selesai',
            ])
            ->when(
                $filter['nama_kegiatan'] ?? null,
                fn ($query, $nama) => $query->where(
                    'nama_kegiatan',
                    'like',
                    "%{$nama}%"
                )
            )
            ->when(
                $filter['tanggal_dibuat'] ?? null,
                fn ($query, $tanggal) => $query->whereDate(
                    'created_at',
                    $tanggal
                )
            )
            ->with('pengguna')
            ->latest()
            ->paginate(10);

        return response()->json([
            'berhasil' => true,
            'pesan' => 'Daftar kegiatan berhasil diambil.',
            'data' => $kegiatan->items(),
            'meta' => [
                'current_page' => $kegiatan->currentPage(),
                'last_page' => $kegiatan->lastPage(),
                'per_page' => $kegiatan->perPage(),
                'total' => $kegiatan->total(),
            ],
        ]);
    }

    /**
     * Menampilkan detail kegiatan yang sudah disetujui atau selesai.
     */
    public function show($id)
    {
        $kegiatan = Kegiatan::whereIn('status', [
                'disetujui',
                'selesai',
            ])
            ->with([
                'pengguna',
                'disetujuiOleh:id,nama',
                'panitia',
                'rab',
                'transaksi',
                'peserta',
                'dokumentasi',
            ])
            ->find($id);

        if (!$kegiatan) {
            return response()->json([
                'berhasil' => false,
                'pesan' => 'Kegiatan tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'berhasil' => true,
            'pesan' => 'Detail kegiatan berhasil diambil.',
            'data' => $kegiatan,
        ]);
    }
}
