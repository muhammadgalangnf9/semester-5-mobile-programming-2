<?php

namespace App\Http\Controllers\Api\KetuaRT;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KelolaWargaApiController extends Controller
{
    private const ROLE_KETUA_RT_ID = 1;

    private const ROLE_WARGA_ID = 2;

    private const ROLE_WARGA_BIASA_ID = 3;

    private function daftarRoleWarga(): array
    {
        return [
            self::ROLE_WARGA_ID,
            self::ROLE_WARGA_BIASA_ID,
        ];
    }

    private function daftarGang(): array
    {
        return [
            'Gang E6',
            'Gang E4/E5',
            'Gang E2/E3',
            'Gang C16/E1',
        ];
    }

    /**
     * Mengambil daftar warga.
     */
    public function index(Request $request)
    {
        $query = Pengguna::with('role')
            ->whereIn('role_id', $this->daftarRoleWarga());

        // Pencarian nama atau username.
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        // Filter berdasarkan gang.
        if ($request->filled('gang')) {
            $query->where('gang', $request->gang);
        }

        if ($request->filled('blok')) {
            $query->where('blok', $request->blok);
        }

        // Filter berdasarkan status.
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $daftarBlokQuery = Pengguna::query()
            ->whereIn('role_id', $this->daftarRoleWarga())
            ->whereNotNull('blok');

        if ($request->filled('gang')) {
            $daftarBlokQuery->where('gang', $request->gang);
        }

        $warga = $query
            ->latest()
            ->paginate(10);

        return response()->json([
            'berhasil' => true,
            'pesan' => 'Daftar warga berhasil diambil.',
            'data' => $warga->items(),
            'daftar_blok' => $daftarBlokQuery
                ->distinct()
                ->orderBy('blok')
                ->pluck('blok')
                ->values(),
            'ketua_rt' => Pengguna::with('role')
                ->where('role_id', self::ROLE_KETUA_RT_ID)
                ->first(),
            'meta' => [
                'current_page' => $warga->currentPage(),
                'last_page' => $warga->lastPage(),
                'per_page' => $warga->perPage(),
                'total' => $warga->total(),
            ],
        ]);
    }

    /**
     * Menambahkan warga baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'username' => [
                'required',
                'string',
                'max:50',
                'unique:pengguna,username',
            ],
            'gang' => [
                'required',
                Rule::in($this->daftarGang()),
            ],
            'blok' => [
                'required',
                'string',
                'max:20',
                function ($attribute, $value, $fail) use ($request) {
                    if (! Pengguna::blokSesuaiGang($request->input('gang'), $value)) {
                        $fail('Blok harus sesuai dengan gang dan berformat awalan/nomor.');
                    }
                },
            ],
            'jenis_kelamin' => [
                'nullable',
                Rule::in(['laki_laki', 'perempuan']),
            ],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'email' => [
                'nullable',
                'email',
                'max:100',
                'unique:pengguna,email',
            ],
            'password' => ['required', 'string', 'min:8'],
            'role_id' => [
                'required',
                'integer',
                Rule::in($this->daftarRoleWarga()),
            ],
        ]);

        $validated['status'] = 'aktif';
        $validated['dinonaktifkan_pada'] = null;

        $warga = Pengguna::create($validated);

        $warga->load('role');

        LogAktivitas::create([
            'pengguna_id' => $request->user()->id,
            'aktivitas' => 'Menambahkan warga',
            'deskripsi' => "Menambahkan akun warga {$warga->nama}.",
        ]);

        return response()->json([
            'berhasil' => true,
            'pesan' => 'Data warga berhasil ditambahkan.',
            'data' => $warga,
        ], 201);
    }

    /**
     * Melihat detail warga.
     */
    public function show(Pengguna $pengguna)
    {
        $this->pastikanAkunDapatDikelola($pengguna);

        $pengguna->load('role');

        return response()->json([
            'berhasil' => true,
            'pesan' => 'Detail warga berhasil diambil.',
            'data' => $pengguna,
        ]);
    }

    /**
     * Memperbarui data warga.
     */
    public function update(Request $request, Pengguna $pengguna)
    {
        $this->pastikanAkunDapatDikelola($pengguna);

        $roleIds = (int) $pengguna->role_id === self::ROLE_KETUA_RT_ID
            ? [self::ROLE_KETUA_RT_ID]
            : $this->daftarRoleWarga();

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'username' => [
                'required',
                'string',
                'max:50',
                Rule::unique('pengguna', 'username')
                    ->ignore($pengguna->id),
            ],
            'gang' => [
                'required',
                Rule::in($this->daftarGang()),
            ],
            'blok' => [
                'required',
                'string',
                'max:20',
                function ($attribute, $value, $fail) use ($request) {
                    if (! Pengguna::blokSesuaiGang($request->input('gang'), $value)) {
                        $fail('Blok harus sesuai dengan gang dan berformat awalan/nomor.');
                    }
                },
            ],
            'jenis_kelamin' => [
                'nullable',
                Rule::in(['laki_laki', 'perempuan']),
            ],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'email' => [
                'nullable',
                'email',
                'max:100',
                Rule::unique('pengguna', 'email')
                    ->ignore($pengguna->id),
            ],
            'password' => ['nullable', 'string', 'min:8'],
            'role_id' => [
                'required',
                'integer',
                Rule::in($roleIds),
            ],
        ]);

        // Password hanya diperbarui jika diisi.
        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $pengguna->update($validated);

        $pengguna->load('role');

        LogAktivitas::create([
            'pengguna_id' => $request->user()->id,
            'aktivitas' => 'Memperbarui data warga',
            'deskripsi' => "Memperbarui data akun warga {$pengguna->nama}.",
        ]);

        return response()->json([
            'berhasil' => true,
            'pesan' => 'Data warga berhasil diperbarui.',
            'data' => $pengguna,
        ]);
    }

    /**
     * Mengaktifkan atau menonaktifkan warga.
     */
    public function ubahStatus(Request $request, Pengguna $pengguna)
    {
        $this->pastikanWarga($pengguna);

        if ($pengguna->status === 'aktif') {
            $pengguna->update([
                'status' => 'nonaktif',
                'dinonaktifkan_pada' => now(),
            ]);

            $pesan = 'Akun warga berhasil dinonaktifkan.';
            $aktivitas = 'Menonaktifkan akun warga';
        } else {
            $pengguna->update([
                'status' => 'aktif',
                'dinonaktifkan_pada' => null,
            ]);

            $pesan = 'Akun warga berhasil diaktifkan.';
            $aktivitas = 'Mengaktifkan akun warga';
        }

        LogAktivitas::create([
            'pengguna_id' => $request->user()->id,
            'aktivitas' => $aktivitas,
            'deskripsi' => "{$aktivitas}: {$pengguna->nama}.",
        ]);

        return response()->json([
            'berhasil' => true,
            'pesan' => $pesan,
            'data' => $pengguna,
        ]);
    }

    /**
     * Memastikan akun merupakan Warga atau Warga Biasa.
     */
    private function pastikanWarga(Pengguna $pengguna): void
    {
        abort_unless(
            in_array(
                (int) $pengguna->role_id,
                $this->daftarRoleWarga(),
                true
            ),
            404
        );
    }

    /**
     * Memastikan akun merupakan Ketua RT atau warga.
     */
    private function pastikanAkunDapatDikelola(Pengguna $pengguna): void
    {
        abort_unless(
            in_array(
                (int) $pengguna->role_id,
                [self::ROLE_KETUA_RT_ID, ...$this->daftarRoleWarga()],
                true
            ),
            404
        );
    }
}
