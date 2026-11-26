<?php

namespace App\Http\Controllers\Api\WargaPJKegiatandanJadwalRonda;

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
                Rule::in([
                    'draft',
                    'menunggu_persetujuan',
                    'terbit',
                    'ditolak',
                    'nonaktif',
                ]),
            ],
        ]);

        $pengumuman = Pengumuman::with(['pengguna:id,nama', 'pengubah:id,nama'])
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
            ->latest()
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

    public function show(Pengumuman $pengumuman)
    {
        return response()->json([
            'berhasil' => true,
            'pesan' => 'Detail pengumuman berhasil diambil.',
            'data' => $pengumuman->load([
                'pengguna:id,nama',
                'pengubah:id,nama',
                'disetujuiOleh:id,nama',
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:150'],
            'isi' => ['required', 'string'],
        ]);

        $pengumuman = Pengumuman::create([
            ...$validated,
            'pengguna_id' => $request->user()->id,
            'pengubah_id' => $request->user()->id,
            'status' => 'draft',
            'diterbitkan_pada' => null,
            'dinonaktifkan_pada' => null,
        ]);

        $this->catatAktivitas(
            $request,
            'Menambahkan pengumuman',
            "Menambahkan pengumuman {$pengumuman->judul}."
        );

        return response()->json([
            'berhasil' => true,
            'pesan' => 'Pengumuman berhasil ditambahkan.',
            'data' => $pengumuman->load(['pengguna:id,nama', 'pengubah:id,nama']),
        ], 201);
    }

    public function update(Request $request, Pengumuman $pengumuman)
    {
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:150'],
            'isi' => ['required', 'string'],
        ]);

        $statusLama = $pengumuman->status;
        $pengumuman->judul = $validated['judul'];
        $pengumuman->isi = $validated['isi'];
        $pengumuman->pengubah_id = $request->user()->id;

        if ($statusLama === 'terbit') {
            $pengumuman->status = 'menunggu_persetujuan';
            $pengumuman->diterbitkan_pada = null;
            $pengumuman->disetujui_oleh = null;
            $pengumuman->catatan_persetujuan = null;
        } elseif (in_array($statusLama, ['ditolak', 'nonaktif'], true)) {
            $pengumuman->status = 'draft';
            $pengumuman->diterbitkan_pada = null;
            $pengumuman->disetujui_oleh = null;
            $pengumuman->catatan_persetujuan = null;
        }

        $pengumuman->dinonaktifkan_pada = null;
        $pengumuman->save();

        $this->catatAktivitas(
            $request,
            'Memperbarui pengumuman',
            "Memperbarui pengumuman {$pengumuman->judul}."
        );

        return response()->json([
            'berhasil' => true,
            'pesan' => 'Pengumuman berhasil diperbarui.',
            'data' => $pengumuman->load(['pengguna:id,nama', 'pengubah:id,nama']),
        ]);
    }

    public function ubahStatus(Request $request, Pengumuman $pengumuman)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['draft', 'nonaktif'])],
        ]);

        $statusLama = $pengumuman->status;
        $statusBaru = $validated['status'];

        $pengumuman->dinonaktifkan_pada = $statusBaru === 'nonaktif'
            ? ($statusLama === 'nonaktif' ? $pengumuman->dinonaktifkan_pada : now())
            : null;
        $pengumuman->status = $statusBaru;
        if ($statusBaru === 'draft') {
            $pengumuman->diterbitkan_pada = null;
            $pengumuman->disetujui_oleh = null;
            $pengumuman->catatan_persetujuan = null;
        }
        $pengumuman->pengubah_id = $request->user()->id;
        $pengumuman->save();

        $this->catatAktivitas(
            $request,
            'Mengubah status pengumuman',
            "Mengubah status pengumuman {$pengumuman->judul} dari {$statusLama} menjadi {$statusBaru}."
        );

        return response()->json([
            'berhasil' => true,
            'pesan' => 'Status pengumuman berhasil diperbarui.',
            'data' => $pengumuman->load(['pengguna:id,nama', 'pengubah:id,nama']),
        ]);
    }

    public function ajukanPersetujuan(Request $request, Pengumuman $pengumuman)
    {
        if (!in_array($pengumuman->status, ['draft', 'ditolak'], true)) {
            return response()->json([
                'berhasil' => false,
                'pesan' => 'Pengumuman tidak dapat diajukan dengan status saat ini.',
            ], 422);
        }

        $pengumuman->update([
            'status' => 'menunggu_persetujuan',
            'pengubah_id' => $request->user()->id,
            'diterbitkan_pada' => null,
            'dinonaktifkan_pada' => null,
            'disetujui_oleh' => null,
            'catatan_persetujuan' => null,
        ]);

        $this->catatAktivitas(
            $request,
            'Mengajukan pengumuman',
            "Mengajukan pengumuman {$pengumuman->judul} untuk persetujuan."
        );

        return response()->json([
            'berhasil' => true,
            'pesan' => 'Pengumuman berhasil diajukan untuk persetujuan.',
            'data' => $pengumuman->load(['pengguna:id,nama', 'pengubah:id,nama']),
        ]);
    }

    public function destroy(Request $request, Pengumuman $pengumuman)
    {
        if ($pengumuman->status !== 'nonaktif') {
            $pengumuman->update([
                'status' => 'nonaktif',
                'pengubah_id' => $request->user()->id,
                'dinonaktifkan_pada' => now(),
            ]);
        }

        $this->catatAktivitas(
            $request,
            'Menonaktifkan pengumuman',
            "Menonaktifkan pengumuman {$pengumuman->judul}."
        );

        return response()->json([
            'berhasil' => true,
            'pesan' => 'Pengumuman berhasil dinonaktifkan.',
            'data' => $pengumuman->load(['pengguna:id,nama', 'pengubah:id,nama']),
        ]);
    }

    private function catatAktivitas(
        Request $request,
        string $aktivitas,
        string $deskripsi
    ): void {
        LogAktivitas::create([
            'pengguna_id' => $request->user()->id,
            'aktivitas' => $aktivitas,
            'deskripsi' => $deskripsi,
        ]);
    }
}
