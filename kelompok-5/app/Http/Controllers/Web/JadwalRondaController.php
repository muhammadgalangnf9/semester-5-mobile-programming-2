<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\JadwalRonda;
use App\Models\LogAktivitas;
use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class JadwalRondaController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Konstanta Role
    |--------------------------------------------------------------------------
    */

    private const ROLE_KETUA_RT_ID = 1;
    private const ROLE_WARGA_ID = 2;
    private const ROLE_WARGA_BIASA_ID = 3;

    private const DAFTAR_HARI = [
        'Senin',
        'Selasa',
        'Rabu',
        'Kamis',
        'Jumat',
        'Sabtu',
        'Minggu',
    ];

    /*
    |--------------------------------------------------------------------------
    | Pemeriksaan Hak Akses
    |--------------------------------------------------------------------------
    */

    /**
     * Memeriksa apakah pengguna boleh mengelola jadwal ronda.
     */
    private function bolehKelola(): bool
    {
        $pengguna = Auth::user();

        return in_array(
            (int) $pengguna->role_id,
            [self::ROLE_WARGA_ID],
            true
        );
    }

    /**
     * Memastikan pengguna memiliki hak pengelolaan.
     */
    private function pastikanBolehKelola(): void
    {
        abort_unless($this->bolehKelola(), 403);
    }

    /**
     * Memastikan pengguna boleh melihat jadwal ronda.
     */
    private function pastikanBolehMelihat(): void
    {
        $pengguna = Auth::user();

        $bolehMelihat =
            (int) $pengguna->role_id === self::ROLE_KETUA_RT_ID
            || (int) $pengguna->role_id === self::ROLE_WARGA_BIASA_ID
            || (int) $pengguna->role_id === self::ROLE_WARGA_ID;

        abort_unless($bolehMelihat, 403);
    }

    /*
    |--------------------------------------------------------------------------
    | Data Gang
    |--------------------------------------------------------------------------
    */

    /**
     * Mengambil daftar gang dari warga aktif.
     */
    private function ambilDaftarGang()
    {
        return Pengguna::query()
            ->whereIn('role_id', [
                self::ROLE_WARGA_ID,
                self::ROLE_WARGA_BIASA_ID,
            ])
            ->where('status', 'aktif')
            ->whereNotNull('gang')
            ->where('gang', '!=', '')
            ->distinct()
            ->orderBy('gang')
            ->pluck('gang');
    }

    /*
    |--------------------------------------------------------------------------
    | Data Peserta
    |--------------------------------------------------------------------------
    */

    /**
     * Mengambil daftar warga aktif yang dapat dipilih sebagai peserta.
     */
    private function ambilDaftarPeserta()
    {
        return Pengguna::query()
            ->whereIn('role_id', [
                self::ROLE_WARGA_ID,
                self::ROLE_WARGA_BIASA_ID,
            ])
            ->where('status', 'aktif')
            ->whereNotNull('gang')
            ->where('gang', '!=', '')
            ->orderBy('gang')
            ->orderBy('nama')
            ->get([
                'id',
                'nama',
                'gang',
            ]);
    }

    /**
     * Mengambil nama peserta untuk pencatatan aktivitas.
     */
    private function ambilNamaPeserta(array $peserta): string
    {
        if (empty($peserta)) {
            return '-';
        }

        return Pengguna::query()
            ->whereIn('id', $peserta)
            ->orderBy('gang')
            ->orderBy('nama')
            ->pluck('nama')
            ->implode(', ');
    }

    /*
    |--------------------------------------------------------------------------
    | Validasi
    |--------------------------------------------------------------------------
    */

    /**
     * Aturan validasi data jadwal ronda.
     */
    private function aturanValidasi(): array
    {
        // Tambahkan Semua Gang sebagai pilihan yang valid.
        $daftarGang = $this->ambilDaftarGang()
            ->push('Semua Gang')
            ->unique()
            ->values()
            ->all();

        return [
            'hari' => [
                'required',
                'array',
                'min:1',
            ],

            'hari.*' => [
                'required',
                'distinct',
                Rule::in(self::DAFTAR_HARI),
            ],

            'peserta' => [
                'required',
                'array',
                'min:1',
            ],

            'peserta.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('pengguna', 'id')
                    ->where(function ($query) {
                        $query->whereIn('role_id', [
                            self::ROLE_WARGA_ID,
                            self::ROLE_WARGA_BIASA_ID,
                        ])->where('status', 'aktif');
                    }),
            ],

            'waktu_mulai' => [
                'required',
                'date_format:H:i',
            ],

            'waktu_selesai' => [
                'required',
                'date_format:H:i',
                'different:waktu_mulai',
            ],

            'gang' => [
                'required',
                'string',
                'max:150',
                Rule::in($daftarGang),
            ],

            'keterangan' => [
                'nullable',
                'string',
            ],
        ];
    }

    /**
     * Memvalidasi data jadwal ronda.
     */
    private function validasiData(Request $request): array
    {
        $validated = $request->validate(
            $this->aturanValidasi()
        );

        // Memastikan seluruh ID peserta disimpan sebagai integer.
        $validated['peserta'] = array_map(
            'intval',
            $validated['peserta']
        );

        return $validated;
    }

    /*
    |--------------------------------------------------------------------------
    | Pencatatan Aktivitas
    |--------------------------------------------------------------------------
    */

    private function catatAktivitas(
        string $aktivitas,
        string $deskripsi
    ): void {
        LogAktivitas::create([
            'pengguna_id' => Auth::id(),
            'aktivitas' => $aktivitas,
            'deskripsi' => $deskripsi,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    /**
     * Menampilkan daftar jadwal ronda.
     */
    public function index()
    {
        $this->pastikanBolehMelihat();

        $query = JadwalRonda::query()
            ->orderByRaw("
                CASE
                    WHEN JSON_CONTAINS(hari, '\"Senin\"') THEN 1
                    WHEN JSON_CONTAINS(hari, '\"Selasa\"') THEN 2
                    WHEN JSON_CONTAINS(hari, '\"Rabu\"') THEN 3
                    WHEN JSON_CONTAINS(hari, '\"Kamis\"') THEN 4
                    WHEN JSON_CONTAINS(hari, '\"Jumat\"') THEN 5
                    WHEN JSON_CONTAINS(hari, '\"Sabtu\"') THEN 6
                    WHEN JSON_CONTAINS(hari, '\"Minggu\"') THEN 7
                    ELSE 8
                END
            ")
            ->orderBy('waktu_mulai');

        if (!$this->bolehKelola()) {
            $query->where('status', 'aktif');
        }

        $jadwalRonda = $query->paginate(10);

        return view('jadwal_ronda.index', compact('jadwalRonda'));
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    /**
     * Menampilkan formulir tambah jadwal ronda.
     */
    public function create()
    {
        $this->pastikanBolehKelola();

        $daftarGang = $this->ambilDaftarGang();
        $daftarPeserta = $this->ambilDaftarPeserta();

        return view('jadwal_ronda.create', compact(
            'daftarGang',
            'daftarPeserta'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    /**
     * Menyimpan jadwal ronda baru.
     */
    public function store(Request $request)
    {
        $this->pastikanBolehKelola();

        $validated = $this->validasiData($request);

        DB::transaction(function () use ($validated) {

            $jadwal = JadwalRonda::create([
                'dibuat_oleh' => Auth::id(),
                'pengubah_id' => Auth::id(),
                'hari' => $validated['hari'],
                'peserta' => $validated['peserta'],
                'waktu_mulai' => $validated['waktu_mulai'],
                'waktu_selesai' => $validated['waktu_selesai'],
                'gang' => $validated['gang'],
                'keterangan' => $validated['keterangan'] ?? null,
                'status' => 'aktif',
                'diterbitkan_pada' => now(),
                'dinonaktifkan_pada' => null,
            ]);

            $hariRonda = count($jadwal->hari) === 7
                ? 'Setiap Hari'
                : implode(', ', $jadwal->hari);

            $namaPeserta = $this->ambilNamaPeserta(
                $jadwal->peserta
            );

            $this->catatAktivitas(
                'Menambahkan jadwal ronda',
                'Menambahkan jadwal ronda pada '
                . $hariRonda
                . ' di '
                . $jadwal->gang
                . ' dengan peserta: '
                . $namaPeserta
            );
        });

        return redirect()
            ->route('jadwal_ronda.index')
            ->with('success', 'Jadwal ronda berhasil ditambahkan.');
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    /**
     * Menampilkan detail jadwal ronda.
     */
    public function show(JadwalRonda $jadwalRonda)
    {
        $this->pastikanBolehMelihat();
        $jadwalRonda->load('pengubah:id,nama');

        if (
            !$this->bolehKelola()
            && $jadwalRonda->status !== 'aktif'
        ) {
            abort(403);
        }

        $pesertaTerpilih = Pengguna::query()
            ->whereIn('id', $jadwalRonda->peserta ?? [])
            ->orderBy('gang')
            ->orderBy('nama')
            ->get([
                'id',
                'nama',
                'gang',
            ]);

        return view('jadwal_ronda.show', compact(
            'jadwalRonda',
            'pesertaTerpilih'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    /**
     * Menampilkan formulir edit jadwal ronda.
     */
    public function edit(JadwalRonda $jadwalRonda)
    {
        $this->pastikanBolehKelola();

        $daftarGang = $this->ambilDaftarGang();
        $daftarPeserta = $this->ambilDaftarPeserta();

        return view('jadwal_ronda.edit', compact(
            'jadwalRonda',
            'daftarGang',
            'daftarPeserta'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    /**
     * Memperbarui data jadwal ronda.
     */
    public function update(
        Request $request,
        JadwalRonda $jadwalRonda
    ) {
        $this->pastikanBolehKelola();

        $validated = $this->validasiData($request);

        DB::transaction(function () use ($validated, $jadwalRonda) {

            $jadwalRonda->update([
                'hari' => $validated['hari'],
                'peserta' => $validated['peserta'],
                'waktu_mulai' => $validated['waktu_mulai'],
                'waktu_selesai' => $validated['waktu_selesai'],
                'gang' => $validated['gang'],
                'keterangan' => $validated['keterangan'] ?? null,
                'pengubah_id' => Auth::id(),
            ]);

            $hariRonda = count($jadwalRonda->hari) === 7
                ? 'Setiap Hari'
                : implode(', ', $jadwalRonda->hari);

            $namaPeserta = $this->ambilNamaPeserta(
                $jadwalRonda->peserta
            );

            $this->catatAktivitas(
                'Memperbarui jadwal ronda',
                'Memperbarui jadwal ronda pada '
                . $hariRonda
                . ' di '
                . $jadwalRonda->gang
                . ' dengan peserta: '
                . $namaPeserta
            );
        });

        return redirect()
            ->route('jadwal_ronda.index')
            ->with('success', 'Jadwal ronda berhasil diperbarui.');
    }

    /*
    |--------------------------------------------------------------------------
    | UBAH STATUS
    |--------------------------------------------------------------------------
    */

    /**
     * Mengaktifkan atau menonaktifkan jadwal ronda.
     */
    public function ubahStatus(JadwalRonda $jadwalRonda)
    {
        $this->pastikanBolehKelola();

        $statusBaru = $jadwalRonda->status === 'aktif'
            ? 'nonaktif'
            : 'aktif';

        DB::transaction(function () use ($jadwalRonda, $statusBaru) {

            $jadwalRonda->update([
                'status' => $statusBaru,
                'dinonaktifkan_pada' => $statusBaru === 'nonaktif'
                    ? now()
                    : null,
                'pengubah_id' => Auth::id(),
            ]);

            $hariRonda = count($jadwalRonda->hari ?? []) === 7
                ? 'Setiap Hari'
                : implode(', ', $jadwalRonda->hari ?? []);

            $keteranganStatus = $statusBaru === 'aktif'
                ? 'Mengaktifkan'
                : 'Menonaktifkan';

            $this->catatAktivitas(
                'Mengubah status jadwal ronda',
                $keteranganStatus
                . ' jadwal ronda pada '
                . $hariRonda
                . ' di '
                . $jadwalRonda->gang
            );
        });

        return redirect()
            ->route('jadwal_ronda.index')
            ->with(
                'success',
                'Status jadwal ronda berhasil diubah menjadi '
                . ucfirst($statusBaru) . '.'
            );
    }
}
