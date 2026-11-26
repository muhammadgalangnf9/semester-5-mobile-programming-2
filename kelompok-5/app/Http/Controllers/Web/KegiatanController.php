<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Kegiatan;
use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class KegiatanController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Konstanta Role
    |--------------------------------------------------------------------------
    */

    private const ROLE_KETUA_RT_ID = 1;
    private const ROLE_WARGA_ID = 2;
    private const ROLE_WARGA_BIASA_ID = 3;

    private const JABATAN_KETUA_PELAKSANA = 'Ketua Pelaksana';

    private const STATUS_KEGIATAN = [
        'draft',
        'terbit',
        'selesai',
        'nonaktif',
    ];

    /*
    |--------------------------------------------------------------------------
    | Hak Akses
    |--------------------------------------------------------------------------
    */

    private function bolehKelola(): bool
    {
        $pengguna = Auth::user();

        if (!$pengguna) {
            return false;
        }

        return in_array(
            (int) $pengguna->role_id,
            [self::ROLE_WARGA_ID],
            true
        );
    }

    private function pastikanBolehKelola(): void
    {
        abort_unless(
            $this->bolehKelola(),
            403,
            'Anda tidak memiliki akses untuk mengelola kegiatan.'
        );
    }

    private function pastikanBolehMelihat(): void
    {
        $pengguna = Auth::user();

        abort_unless(
            $pengguna
            && in_array(
                (int) $pengguna->role_id,
                [
                    self::ROLE_KETUA_RT_ID,
                    self::ROLE_WARGA_ID,
                    self::ROLE_WARGA_BIASA_ID,
                ],
                true
            ),
            403,
            'Anda tidak memiliki akses untuk melihat kegiatan.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Daftar Gang
    |--------------------------------------------------------------------------
    */

    private function daftarGang()
    {
        return Pengguna::whereNotNull('gang')
            ->where('gang', '!=', '')
            ->distinct()
            ->orderBy('gang')
            ->pluck('gang');
    }

    /*
    |--------------------------------------------------------------------------
    | Persiapan Lokasi
    |--------------------------------------------------------------------------
    */

    private function siapkanLokasi(Request $request): void
    {
        if ($request->input('lokasi') === '__lainnya__') {
            $request->merge([
                'lokasi' => trim(
                    (string) $request->input('lokasi_lainnya')
                ),
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Pemeriksaan Ketua Pelaksana
    |--------------------------------------------------------------------------
    */

    private function memilikiKetuaPelaksana(Kegiatan $kegiatan): bool
    {
        return $kegiatan->panitia()
            ->where('jabatan', self::JABATAN_KETUA_PELAKSANA)
            ->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | Pengaturan Waktu Status
    |--------------------------------------------------------------------------
    */

    private function dataWaktuStatus(
        string $status,
        ?Kegiatan $kegiatan = null
    ): array {
        $diterbitkanPada = $kegiatan?->diterbitkan_pada;
        $dinonaktifkanPada = null;

        if (in_array($status, ['terbit', 'selesai'], true)) {
            $diterbitkanPada ??= now();
        }

        if ($status === 'nonaktif') {
            $dinonaktifkanPada = now();
        }

        return [
            'status' => $status,
            'diterbitkan_pada' => $diterbitkanPada,
            'dinonaktifkan_pada' => $dinonaktifkanPada,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Daftar Kegiatan
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $this->pastikanBolehMelihat();

        $query = Kegiatan::with('pengguna')
            ->latest();

        // Warga hanya dapat melihat kegiatan terbit dan selesai.
        if (!$this->bolehKelola()) {
            $query->whereIn('status', ['terbit', 'selesai']);
        }

        $kegiatan = $query->paginate(10);

        return view('kegiatan.index', compact('kegiatan'));
    }

    /*
    |--------------------------------------------------------------------------
    | Form Tambah Kegiatan
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $this->pastikanBolehKelola();

        $daftarGang = $this->daftarGang();

        return view('kegiatan.create', compact('daftarGang'));
    }

    /*
    |--------------------------------------------------------------------------
    | Simpan Kegiatan
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $this->pastikanBolehKelola();

        $this->siapkanLokasi($request);

        $validated = $request->validate([
            'nama_kegiatan' => [
                'required',
                'string',
                'max:150',
            ],

            'deskripsi' => [
                'nullable',
                'string',
            ],

            'jenis_kegiatan' => [
                'required',
                'string',
                'max:100',
            ],

            'tanggal_mulai' => [
                'required',
                'date',
            ],

            'tanggal_selesai' => [
                'required',
                'date',
                'after_or_equal:tanggal_mulai',
            ],

            'lokasi' => [
                'required',
                'string',
                'max:150',
            ],

            'link_poster' => [
                'nullable',
                'url',
                'max:255',
            ],

            'status' => [
                'required',
                Rule::in(['draft']),
            ],
        ], [
            'nama_kegiatan.required' => 'Nama kegiatan wajib diisi.',
            'deskripsi.string' => 'Deskripsi kegiatan harus berupa teks.',
            'jenis_kegiatan.required' => 'Jenis kegiatan wajib dipilih.',
            'tanggal_mulai.required' => 'Tanggal mulai wajib diisi.',
            'tanggal_selesai.required' => 'Tanggal selesai wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'lokasi.required' => 'Lokasi kegiatan wajib diisi.',
            'link_poster.url' => 'Link poster harus berupa URL yang valid.',
            'status.in' => 'Kegiatan baru harus disimpan sebagai draft terlebih dahulu.',
        ]);

        $kegiatan = Kegiatan::create([
            'dibuat_oleh' => Auth::id(),
            'pengubah_id' => Auth::id(),
            'nama_kegiatan' => $validated['nama_kegiatan'],
            'deskripsi' => $validated['deskripsi'] ?? null,
            'jenis_kegiatan' => $validated['jenis_kegiatan'],
            'tanggal_mulai' => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'],
            'lokasi' => $validated['lokasi'],
            'link_poster' => $validated['link_poster'] ?? null,
            'status' => 'draft',
            'diterbitkan_pada' => null,
            'dinonaktifkan_pada' => null,
        ]);

        return redirect()
            ->route('kegiatan.show', $kegiatan->id)
            ->with(
                'success',
                'Kegiatan berhasil ditambahkan sebagai draft. Silakan lengkapi panitia sebelum menerbitkan kegiatan.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Detail Kegiatan
    |--------------------------------------------------------------------------
    */

    public function show(Kegiatan $kegiatan)
    {
        $this->pastikanBolehMelihat();

        // Warga tidak boleh membuka kegiatan draft atau nonaktif.
        if (
            !$this->bolehKelola()
            && !in_array($kegiatan->status, ['terbit', 'selesai'], true)
        ) {
            abort(404);
        }

        $kegiatan->load([
            'pengguna',
            'pengaju',
            'pengubah',
            'panitia',
            'rab',
            'transaksi',
            'peserta',
            'dokumentasi',
        ]);

        $daftarGang = $this->daftarGang();

        return view('kegiatan.show', compact(
            'kegiatan',
            'daftarGang'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Form Edit Kegiatan
    |--------------------------------------------------------------------------
    */

    public function edit(Kegiatan $kegiatan)
    {
        $this->pastikanBolehKelola();

        $daftarGang = $this->daftarGang();

        return view('kegiatan.edit', compact(
            'kegiatan',
            'daftarGang'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Update Kegiatan
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, Kegiatan $kegiatan)
    {
        $this->pastikanBolehKelola();

        $this->siapkanLokasi($request);

        $validated = $request->validate([
            'nama_kegiatan' => [
                'required',
                'string',
                'max:150',
            ],

            'deskripsi' => [
                'nullable',
                'string',
            ],

            'jenis_kegiatan' => [
                'required',
                'string',
                'max:100',
            ],

            'tanggal_mulai' => [
                'required',
                'date',
            ],

            'tanggal_selesai' => [
                'required',
                'date',
                'after_or_equal:tanggal_mulai',
            ],

            'lokasi' => [
                'required',
                'string',
                'max:150',
            ],

            'link_poster' => [
                'nullable',
                'url',
                'max:255',
            ],
        ], [
            'nama_kegiatan.required' => 'Nama kegiatan wajib diisi.',
            'deskripsi.string' => 'Deskripsi kegiatan harus berupa teks.',
            'jenis_kegiatan.required' => 'Jenis kegiatan wajib dipilih.',
            'tanggal_mulai.required' => 'Tanggal mulai wajib diisi.',
            'tanggal_selesai.required' => 'Tanggal selesai wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'lokasi.required' => 'Lokasi kegiatan wajib diisi.',
            'link_poster.url' => 'Link poster harus berupa URL yang valid.',
        ]);

        $kegiatan->update([
            'pengubah_id' => Auth::id(),
            'nama_kegiatan' => $validated['nama_kegiatan'],
            'deskripsi' => $validated['deskripsi'] ?? null,
            'jenis_kegiatan' => $validated['jenis_kegiatan'],
            'tanggal_mulai' => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'],
            'lokasi' => $validated['lokasi'],
            'link_poster' => $validated['link_poster'] ?? null,
        ]);

        return redirect()
            ->route('kegiatan.show', $kegiatan->id)
            ->with('success', 'Data kegiatan berhasil diperbarui.');
    }

    /*
    |--------------------------------------------------------------------------
    | Ubah Status Kegiatan
    |--------------------------------------------------------------------------
    */

    public function ubahStatus(Request $request, Kegiatan $kegiatan)
    {
        $this->pastikanBolehKelola();

        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in(self::STATUS_KEGIATAN),
            ],
        ], [
            'status.required' => 'Status kegiatan wajib dipilih.',
            'status.in' => 'Status kegiatan tidak valid.',
        ]);

        $statusBaru = $validated['status'];

        // Kegiatan harus memiliki Ketua Pelaksana sebelum diterbitkan.
        if (
            $statusBaru === 'terbit'
            && !$this->memilikiKetuaPelaksana($kegiatan)
        ) {
            return back()->with(
                'error',
                'Kegiatan belum dapat diterbitkan karena belum memiliki Ketua Pelaksana.'
            );
        }

        // Kegiatan harus pernah diterbitkan sebelum dapat diselesaikan.
        if (
            $statusBaru === 'selesai'
            && !$kegiatan->diterbitkan_pada
        ) {
            return back()->with(
                'error',
                'Kegiatan harus diterbitkan terlebih dahulu sebelum ditandai selesai.'
            );
        }

        $statusData = $this->dataWaktuStatus(
            $statusBaru,
            $kegiatan
        );
        $statusData['pengubah_id'] = Auth::id();

        $kegiatan->update($statusData);

        return redirect()
            ->route('kegiatan.show', $kegiatan->id)
            ->with('success', 'Status kegiatan berhasil diperbarui.');
    }
}
