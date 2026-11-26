<?php

namespace App\Http\Controllers\Api\KetuaRT;

use App\Http\Controllers\Controller;
use App\Models\JadwalRonda;
use App\Models\Pengguna;
use Illuminate\Http\Request;

class JadwalRondaApiController extends Controller
{
    /**
     * Menampilkan daftar jadwal ronda yang aktif.
     */
    public function index(Request $request)
    {
        $jadwalRonda = JadwalRonda::where('status', 'aktif')
            ->orderBy('hari')
            ->orderBy('waktu_mulai')
            ->paginate(10);

        $daftarJadwal = collect($jadwalRonda->items());
        $idPeserta = $daftarJadwal
            ->flatMap(fn (JadwalRonda $jadwal) => $jadwal->peserta ?? [])
            ->unique()
            ->values();
        $pesertaById = Pengguna::whereIn('id', $idPeserta)
            ->get(['id', 'nama', 'gang', 'blok'])
            ->keyBy('id');

        $daftarJadwal->each(function (JadwalRonda $jadwal) use ($pesertaById) {
            $jadwal->setAttribute(
                'peserta',
                collect($jadwal->peserta ?? [])
                    ->map(fn ($id) => $pesertaById->get($id))
                    ->filter()
                    ->values()
            );
        });

        return response()->json([
            'berhasil' => true,
            'pesan' => 'Daftar jadwal ronda berhasil diambil.',
            'data' => $daftarJadwal->values(),
            'meta' => [
                'current_page' => $jadwalRonda->currentPage(),
                'last_page' => $jadwalRonda->lastPage(),
                'per_page' => $jadwalRonda->perPage(),
                'total' => $jadwalRonda->total(),
            ],
        ]);
    }

    /**
     * Menampilkan detail jadwal ronda.
     */
    public function show($id)
    {
        $jadwalRonda = JadwalRonda::where('status', 'aktif')
            ->with(['pengguna:id,nama', 'pengubah:id,nama'])
            ->find($id);

        if (!$jadwalRonda) {
            return response()->json([
                'berhasil' => false,
                'pesan' => 'Jadwal ronda tidak ditemukan.',
            ], 404);
        }

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

        return response()->json([
            'berhasil' => true,
            'pesan' => 'Detail jadwal ronda berhasil diambil.',
            'data' => $jadwalRonda,
        ]);
    }
}
