<?php

namespace App\Http\Controllers\Api\KetuaRT;

use App\Http\Controllers\Controller;
use App\Models\Kegiatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KegiatanApiController extends Controller
{
    /**
     * Menampilkan seluruh kegiatan kecuali yang nonaktif.
     */
    public function index(Request $request)
    {
        $filter = $request->validate([
            'nama_kegiatan' => ['nullable', 'string', 'max:150'],
            'tanggal_dibuat' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $kegiatan = Kegiatan::where('status', '!=', 'nonaktif')
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
     * Menampilkan detail kegiatan.
     */
    public function show($id)
    {
        $kegiatan = Kegiatan::where('status', '!=', 'nonaktif')
            ->with([
                'pengguna',
                'pengaju',
                'pengubah',
                'disetujuiOleh',
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

    /**
     * Menyetujui kegiatan.
     */
    public function setujui(Request $request, $id)
    {
        $kegiatan = Kegiatan::where(
            'status',
            'menunggu_persetujuan'
        )->find($id);

        if (!$kegiatan) {
            return response()->json([
                'berhasil' => false,
                'pesan' => 'Kegiatan tidak ditemukan atau belum menunggu persetujuan.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'catatan_persetujuan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'berhasil' => false,
                'pesan' => 'Data persetujuan tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $kegiatan->update([
            'status' => 'disetujui',
            'pengubah_id' => $request->user()->id,
            'disetujui_oleh' => $request->user()->id,
            'catatan_persetujuan' => $request->catatan_persetujuan,
            'diterbitkan_pada' => now(),
            'dinonaktifkan_pada' => null,
        ]);

        return response()->json([
            'berhasil' => true,
            'pesan' => 'Kegiatan berhasil disetujui.',
            'data' => $kegiatan,
        ]);
    }

    /**
     * Menolak kegiatan.
     */
    public function tolak(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'catatan_persetujuan' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'berhasil' => false,
                'pesan' => 'Alasan penolakan wajib diisi.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $kegiatan = Kegiatan::where(
            'status',
            'menunggu_persetujuan'
        )->find($id);

        if (!$kegiatan) {
            return response()->json([
                'berhasil' => false,
                'pesan' => 'Kegiatan tidak ditemukan atau belum menunggu persetujuan.',
            ], 404);
        }

        $kegiatan->update([
            'status' => 'ditolak',
            'pengubah_id' => $request->user()->id,
            'disetujui_oleh' => null,
            'catatan_persetujuan' => $request->catatan_persetujuan,
            'diterbitkan_pada' => null,
        ]);

        return response()->json([
            'berhasil' => true,
            'pesan' => 'Kegiatan berhasil ditolak.',
            'data' => $kegiatan,
        ]);
    }

    /**
     * Membatalkan persetujuan kegiatan.
     */
    public function batalkanPersetujuan(Request $request, $id)
    {
        $kegiatan = Kegiatan::where(
            'status',
            'disetujui'
        )->find($id);

        if (!$kegiatan) {
            return response()->json([
                'berhasil' => false,
                'pesan' => 'Kegiatan tidak ditemukan atau belum disetujui.',
            ], 404);
        }

        $kegiatan->update([
            'status' => 'menunggu_persetujuan',
            'pengubah_id' => $request->user()->id,
            'disetujui_oleh' => null,
            'catatan_persetujuan' => null,
            'diterbitkan_pada' => null,
        ]);

        return response()->json([
            'berhasil' => true,
            'pesan' => 'Persetujuan kegiatan berhasil dibatalkan.',
            'data' => $kegiatan,
        ]);
    }
}
