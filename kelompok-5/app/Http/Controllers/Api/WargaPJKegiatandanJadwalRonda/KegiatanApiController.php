<?php

namespace App\Http\Controllers\Api\WargaPJKegiatandanJadwalRonda;

use App\Http\Controllers\Controller;
use App\Models\Kegiatan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class KegiatanApiController extends Controller
{
    private function kegiatanSesamaRole(Request $request): Builder
    {
        $roleId = $request->user()->role_id;

        return Kegiatan::whereHas(
            'pengguna',
            fn ($query) => $query->where('role_id', $roleId)
        );
    }

    /**
     * Menampilkan kegiatan yang dibuat oleh sesama Warga PJ.
     */
    public function index(Request $request)
    {
        $filter = $request->validate([
            'nama_kegiatan' => ['nullable', 'string', 'max:150'],
            'tanggal_dibuat' => ['nullable', 'date_format:Y-m-d'],
            'status' => [
                'nullable',
                Rule::in([
                    'draft',
                    'menunggu_persetujuan',
                    'disetujui',
                    'ditolak',
                    'nonaktif',
                ]),
            ],
        ]);

        $kegiatan = $this->kegiatanSesamaRole($request)
            ->with('pengguna:id,nama')
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
            ->when(
                $filter['status'] ?? null,
                fn ($query, $status) => $query->where('status', $status)
            )
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
     * Menampilkan detail kegiatan yang dibuat oleh sesama Warga PJ.
     */
    public function show(Request $request, $id)
    {
        $kegiatan = $this->kegiatanSesamaRole($request)
            ->with([
                'pengguna:id,nama',
                'pengaju:id,nama',
                'pengubah:id,nama',
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

    /**
     * Validasi data kegiatan.
     */
    private function validasi(Request $request)
    {
        return Validator::make($request->all(), [
            'nama_kegiatan' => 'required|string|max:150',
            'deskripsi' => 'required|string',
            'jenis_kegiatan' => 'required|string|max:100',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'lokasi' => 'required|string|max:150',
            'link_poster' => 'nullable|url|max:255',
        ]);
    }

    /**
     * Membuat kegiatan baru.
     */
    public function store(Request $request)
    {
        $validator = $this->validasi($request);

        if ($validator->fails()) {
            return response()->json([
                'berhasil' => false,
                'pesan' => 'Data kegiatan tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $kegiatan = Kegiatan::create([
            'dibuat_oleh' => $request->user()->id,
            'pengubah_id' => $request->user()->id,
            'nama_kegiatan' => $request->nama_kegiatan,
            'deskripsi' => $request->deskripsi,
            'jenis_kegiatan' => $request->jenis_kegiatan,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'lokasi' => $request->lokasi,
            'link_poster' => $request->link_poster,
            'status' => 'draft',
        ]);

        return response()->json([
            'berhasil' => true,
            'pesan' => 'Kegiatan berhasil dibuat sebagai draft.',
            'data' => $kegiatan->load('pengguna:id,nama'),
        ], 201);
    }

    /**
     * Mengedit kegiatan yang dibuat oleh sesama Warga PJ.
     */
    public function update(Request $request, $id)
    {
        $kegiatan = $this->kegiatanSesamaRole($request)
            ->where('status', '!=', 'nonaktif')
            ->find($id);

        if (!$kegiatan) {
            return response()->json([
                'berhasil' => false,
                'pesan' => 'Kegiatan tidak ditemukan.',
            ], 404);
        }

        if ($kegiatan->status === 'selesai') {
            return response()->json([
                'berhasil' => false,
                'pesan' => 'Kegiatan yang sudah selesai tidak dapat diedit.',
            ], 422);
        }

        $validator = $this->validasi($request);

        if ($validator->fails()) {
            return response()->json([
                'berhasil' => false,
                'pesan' => 'Data kegiatan tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $statusLama = $kegiatan->status;

        $kegiatan->nama_kegiatan = $request->nama_kegiatan;
        $kegiatan->pengubah_id = $request->user()->id;
        $kegiatan->deskripsi = $request->deskripsi;
        $kegiatan->jenis_kegiatan = $request->jenis_kegiatan;
        $kegiatan->tanggal_mulai = $request->tanggal_mulai;
        $kegiatan->tanggal_selesai = $request->tanggal_selesai;
        $kegiatan->lokasi = $request->lokasi;
        $kegiatan->link_poster = $request->link_poster;

        // Jika kegiatan sebelumnya sudah disetujui,
        // perubahan harus diperiksa ulang oleh Ketua RT.
        if ($statusLama === 'disetujui') {
            $kegiatan->status = 'menunggu_persetujuan';
            $kegiatan->pengaju_id = $request->user()->id;
            $kegiatan->diterbitkan_pada = null;
            $kegiatan->disetujui_oleh = null;
            $kegiatan->catatan_persetujuan = null;
        }

        // Jika kegiatan ditolak dan diedit,
        // status kembali menjadi draft.
        if ($statusLama === 'ditolak') {
            $kegiatan->status = 'draft';
            $kegiatan->pengaju_id = null;
            $kegiatan->catatan_persetujuan = null;
            $kegiatan->disetujui_oleh = null;
        }

        $kegiatan->save();

        return response()->json([
            'berhasil' => true,
            'pesan' => $statusLama === 'disetujui'
                ? 'Kegiatan diperbarui dan menunggu persetujuan ulang.'
                : 'Kegiatan berhasil diperbarui.',
            'data' => $kegiatan->load('pengguna:id,nama'),
        ]);
    }

    /**
     * Mengajukan kegiatan kepada Ketua RT.
     */
    public function ajukan(Request $request, $id)
    {
        $kegiatan = $this->kegiatanSesamaRole($request)
            ->find($id);

        if (!$kegiatan) {
            return response()->json([
                'berhasil' => false,
                'pesan' => 'Kegiatan tidak ditemukan.',
            ], 404);
        }

        if (!in_array($kegiatan->status, ['draft', 'ditolak'])) {
            return response()->json([
                'berhasil' => false,
                'pesan' => 'Kegiatan tidak dapat diajukan dengan status saat ini.',
            ], 422);
        }

        $kegiatan->update([
            'status' => 'menunggu_persetujuan',
            'pengaju_id' => $request->user()->id,
            'pengubah_id' => $request->user()->id,
            'disetujui_oleh' => null,
            'catatan_persetujuan' => null,
            'diterbitkan_pada' => null,
        ]);

        return response()->json([
            'berhasil' => true,
            'pesan' => 'Kegiatan berhasil diajukan untuk persetujuan.',
            'data' => $kegiatan,
        ]);
    }

    /**
     * Menonaktifkan kegiatan.
     */
    public function destroy(Request $request, $id)
    {
        $kegiatan = $this->kegiatanSesamaRole($request)
            ->where('status', '!=', 'nonaktif')
            ->find($id);

        if (!$kegiatan) {
            return response()->json([
                'berhasil' => false,
                'pesan' => 'Kegiatan tidak ditemukan.',
            ], 404);
        }

        $kegiatan->update([
            'status' => 'nonaktif',
            'pengubah_id' => $request->user()->id,
            'dinonaktifkan_pada' => now(),
        ]);

        return response()->json([
            'berhasil' => true,
            'pesan' => 'Kegiatan berhasil dinonaktifkan.',
        ]);
    }

    /**
     * Mengaktifkan kembali kegiatan sebagai draft agar dapat diajukan ulang.
     */
    public function aktifkan(Request $request, $id)
    {
        $kegiatan = $this->kegiatanSesamaRole($request)
            ->where('status', 'nonaktif')
            ->find($id);

        if (!$kegiatan) {
            return response()->json([
                'berhasil' => false,
                'pesan' => 'Kegiatan tidak ditemukan atau belum berstatus nonaktif.',
            ], 404);
        }

        $kegiatan->update([
            'status' => 'draft',
            'pengaju_id' => null,
            'pengubah_id' => $request->user()->id,
            'diterbitkan_pada' => null,
            'disetujui_oleh' => null,
            'catatan_persetujuan' => null,
            'dinonaktifkan_pada' => null,
        ]);

        return response()->json([
            'berhasil' => true,
            'pesan' => 'Kegiatan diaktifkan kembali sebagai draft.',
            'data' => $kegiatan,
        ]);
    }
}
