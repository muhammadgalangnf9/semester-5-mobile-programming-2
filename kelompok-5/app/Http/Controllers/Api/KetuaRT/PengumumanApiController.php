<?php

namespace App\Http\Controllers\Api\KetuaRT;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use App\Models\Pengumuman;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PengumumanApiController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->validate([
            'nama_pengumuman' => ['nullable', 'string', 'max:150'],
            'tanggal_dibuat' => ['nullable', 'date_format:Y-m-d'],
            'status' => [
                'nullable',
                Rule::in(['menunggu_persetujuan', 'terbit']),
            ],
        ]);

        $pengumuman = Pengumuman::with([
            'pengguna:id,nama',
            'pengubah:id,nama',
            'disetujuiOleh:id,nama',
        ])
            ->whereIn('status', ['menunggu_persetujuan', 'terbit'])
            ->when(
                $filter['nama_pengumuman'] ?? null,
                fn ($query, $nama) => $query->where('judul', 'like', "%{$nama}%")
            )
            ->when(
                $filter['tanggal_dibuat'] ?? null,
                fn ($query, $tanggal) => $query->whereDate('created_at', $tanggal)
            )
            ->when(
                $filter['status'] ?? null,
                fn ($query, $status) => $query->where('status', $status)
            )
            ->orderByRaw("CASE WHEN status = 'menunggu_persetujuan' THEN 0 ELSE 1 END")
            ->orderByDesc('updated_at')
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

    public function show($id)
    {
        $pengumuman = Pengumuman::with([
            'pengguna:id,nama',
            'pengubah:id,nama',
            'disetujuiOleh:id,nama',
        ])
            ->whereIn('status', ['menunggu_persetujuan', 'terbit'])
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

    public function setujui(Request $request, $id)
    {
        $validated = $request->validate([
            'catatan_persetujuan' => ['nullable', 'string'],
        ]);

        $pengumuman = Pengumuman::where('status', 'menunggu_persetujuan')->find($id);
        if (!$pengumuman) {
            return response()->json([
                'berhasil' => false,
                'pesan' => 'Pengumuman tidak ditemukan atau sudah diproses.',
            ], 404);
        }

        $pengumuman->update([
            'status' => 'terbit',
            'pengubah_id' => $request->user()->id,
            'disetujui_oleh' => $request->user()->id,
            'catatan_persetujuan' => $validated['catatan_persetujuan'] ?? null,
            'diterbitkan_pada' => now(),
            'dinonaktifkan_pada' => null,
        ]);

        LogAktivitas::create([
            'pengguna_id' => $request->user()->id,
            'aktivitas' => 'Menyetujui pengumuman',
            'deskripsi' => "Menyetujui pengumuman {$pengumuman->judul}.",
        ]);

        return response()->json([
            'berhasil' => true,
            'pesan' => 'Pengumuman berhasil disetujui dan diterbitkan.',
            'data' => $pengumuman->load([
                'pengguna:id,nama',
                'pengubah:id,nama',
                'disetujuiOleh:id,nama',
            ]),
        ]);
    }

    public function tolak(Request $request, $id)
    {
        $validated = $request->validate([
            'catatan_persetujuan' => ['required', 'string'],
        ]);

        $pengumuman = Pengumuman::where('status', 'menunggu_persetujuan')->find($id);
        if (!$pengumuman) {
            return response()->json([
                'berhasil' => false,
                'pesan' => 'Pengumuman tidak ditemukan atau sudah diproses.',
            ], 404);
        }

        $pengumuman->update([
            'status' => 'ditolak',
            'pengubah_id' => $request->user()->id,
            'disetujui_oleh' => null,
            'catatan_persetujuan' => $validated['catatan_persetujuan'],
            'diterbitkan_pada' => null,
        ]);

        LogAktivitas::create([
            'pengguna_id' => $request->user()->id,
            'aktivitas' => 'Menolak pengumuman',
            'deskripsi' => "Menolak pengumuman {$pengumuman->judul}.",
        ]);

        return response()->json([
            'berhasil' => true,
            'pesan' => 'Pengumuman berhasil ditolak.',
            'data' => $pengumuman->load([
                'pengguna:id,nama',
                'pengubah:id,nama',
                'disetujuiOleh:id,nama',
            ]),
        ]);
    }
}
