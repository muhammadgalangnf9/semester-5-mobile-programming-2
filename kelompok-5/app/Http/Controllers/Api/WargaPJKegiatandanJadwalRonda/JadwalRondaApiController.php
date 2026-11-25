<?php

namespace App\Http\Controllers\Api\WargaPJKegiatandanJadwalRonda;

use App\Http\Controllers\Controller;
use App\Models\JadwalRonda;
use App\Models\LogAktivitas;
use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class JadwalRondaApiController extends Controller
{
    private const DAFTAR_HARI = [
        'Senin',
        'Selasa',
        'Rabu',
        'Kamis',
        'Jumat',
        'Sabtu',
        'Minggu',
    ];

    public function index(Request $request)
    {
        $filter = $request->validate([
            'gang' => ['nullable', 'string', 'max:150'],
            'hari' => ['nullable', Rule::in(self::DAFTAR_HARI)],
        ]);

        $jadwalRonda = JadwalRonda::query()
            ->with('pengguna:id,nama')
            ->when(
                $filter['gang'] ?? null,
                fn ($query, $gang) => $query->where('gang', $gang)
            )
            ->when(
                $filter['hari'] ?? null,
                fn ($query, $hari) => $query->whereJsonContains('hari', $hari)
            )
            ->orderBy('waktu_mulai')
            ->paginate(10);

        $jadwalRonda->getCollection()->each(
            fn (JadwalRonda $jadwal) => $this->muatPeserta($jadwal)
        );

        return response()->json([
            'berhasil' => true,
            'pesan' => 'Daftar jadwal ronda berhasil diambil.',
            'data' => $jadwalRonda->items(),
            'meta' => [
                'current_page' => $jadwalRonda->currentPage(),
                'last_page' => $jadwalRonda->lastPage(),
                'per_page' => $jadwalRonda->perPage(),
                'total' => $jadwalRonda->total(),
            ],
        ]);
    }

    public function show(JadwalRonda $jadwalRonda)
    {
        return response()->json([
            'berhasil' => true,
            'pesan' => 'Detail jadwal ronda berhasil diambil.',
            'data' => $this->muatPeserta(
                $jadwalRonda->load('pengguna:id,nama')
            ),
        ]);
    }

    public function daftarPeserta()
    {
        $peserta = Pengguna::query()
            ->whereIn('role_id', [2, 3])
            ->where('status', 'aktif')
            ->orderBy('gang')
            ->orderBy('blok')
            ->orderBy('nama')
            ->get(['id', 'nama', 'gang', 'blok']);

        return response()->json([
            'berhasil' => true,
            'pesan' => 'Daftar peserta ronda berhasil diambil.',
            'data' => $peserta,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validasi($request);

        $jadwalRonda = DB::transaction(function () use ($validated, $request) {
            $jadwal = JadwalRonda::create([
                ...$validated,
                'dibuat_oleh' => $request->user()->id,
                'status' => 'aktif',
                'diterbitkan_pada' => now(),
                'dinonaktifkan_pada' => null,
            ]);

            $this->catatAktivitas(
                $request,
                'Menambahkan jadwal ronda',
                $this->deskripsiJadwal('Menambahkan', $jadwal)
            );

            return $jadwal;
        });

        return response()->json([
            'berhasil' => true,
            'pesan' => 'Jadwal ronda berhasil ditambahkan.',
            'data' => $this->muatPeserta($jadwalRonda),
        ], 201);
    }

    public function update(Request $request, JadwalRonda $jadwalRonda)
    {
        $validated = $this->validasi($request);

        DB::transaction(function () use ($validated, $request, $jadwalRonda) {
            $jadwalRonda->update($validated);

            $this->catatAktivitas(
                $request,
                'Memperbarui jadwal ronda',
                $this->deskripsiJadwal('Memperbarui', $jadwalRonda)
            );
        });

        return response()->json([
            'berhasil' => true,
            'pesan' => 'Jadwal ronda berhasil diperbarui.',
            'data' => $this->muatPeserta($jadwalRonda->refresh()),
        ]);
    }

    public function ubahStatus(Request $request, JadwalRonda $jadwalRonda)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
        ]);

        return $this->simpanStatus($request, $jadwalRonda, $validated['status']);
    }

    public function destroy(Request $request, JadwalRonda $jadwalRonda)
    {
        return $this->simpanStatus($request, $jadwalRonda, 'nonaktif');
    }

    private function validasi(Request $request): array
    {
        $daftarGang = Pengguna::query()
            ->whereIn('role_id', [2, 3])
            ->where('status', 'aktif')
            ->whereNotNull('gang')
            ->distinct()
            ->orderBy('gang')
            ->pluck('gang')
            ->push('Semua Gang')
            ->unique()
            ->values()
            ->all();

        $validated = $request->validate([
            'hari' => ['required', 'array', 'min:1'],
            'hari.*' => ['required', 'distinct', Rule::in(self::DAFTAR_HARI)],
            'peserta' => ['required', 'array', 'min:1'],
            'peserta.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('pengguna', 'id')->where(
                    fn ($query) => $query
                        ->whereIn('role_id', [2, 3])
                        ->where('status', 'aktif')
                ),
            ],
            'waktu_mulai' => ['required', 'date_format:H:i'],
            'waktu_selesai' => [
                'required',
                'date_format:H:i',
                'different:waktu_mulai',
            ],
            'gang' => ['required', 'string', 'max:150', Rule::in($daftarGang)],
            'keterangan' => ['nullable', 'string'],
        ]);

        $validated['peserta'] = array_map('intval', $validated['peserta']);

        return $validated;
    }

    private function simpanStatus(
        Request $request,
        JadwalRonda $jadwalRonda,
        string $status
    ) {
        $jadwalRonda->update([
            'status' => $status,
            'dinonaktifkan_pada' => $status === 'nonaktif' ? now() : null,
        ]);

        $aksi = $status === 'aktif' ? 'Mengaktifkan' : 'Menonaktifkan';
        $this->catatAktivitas(
            $request,
            'Mengubah status jadwal ronda',
            $this->deskripsiJadwal($aksi, $jadwalRonda)
        );

        $pesan = $status === 'aktif'
            ? 'Jadwal ronda berhasil diaktifkan.'
            : 'Jadwal ronda berhasil dinonaktifkan.';

        return response()->json([
            'berhasil' => true,
            'pesan' => $pesan,
            'data' => $this->muatPeserta($jadwalRonda),
        ]);
    }

    private function muatPeserta(JadwalRonda $jadwalRonda): JadwalRonda
    {
        $jadwalRonda->loadMissing('pengguna:id,nama');

        $pesertaById = Pengguna::whereIn('id', $jadwalRonda->peserta ?? [])
            ->get(['id', 'nama', 'gang', 'blok'])
            ->keyBy('id');

        $jadwalRonda->setAttribute(
            'peserta',
            collect($jadwalRonda->peserta ?? [])
                ->map(fn ($id) => $pesertaById->get($id))
                ->filter()
                ->values()
        );

        return $jadwalRonda;
    }

    private function deskripsiJadwal(string $aksi, JadwalRonda $jadwal): string
    {
        $hari = count($jadwal->hari ?? []) === 7
            ? 'Setiap Hari'
            : implode(', ', $jadwal->hari ?? []);

        return "{$aksi} jadwal ronda pada {$hari} di {$jadwal->gang}.";
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
