<?php

namespace App\Http\Controllers\Web\KetuaRT;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use App\Models\Pengguna;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class KelolaWargaController extends Controller
{
    /**
     * ID role berdasarkan data awal tabel role.
     */
    private const ROLE_KETUA_RT_ID = 1;

    private const ROLE_WARGA_ID = 2;

    private const ROLE_WARGA_BIASA_ID = 3;

    /**
     * Daftar ID role yang boleh digunakan untuk akun warga.
     */
    private function daftarRoleWarga()
    {
        return Role::whereIn('id', [
            self::ROLE_WARGA_ID,
            self::ROLE_WARGA_BIASA_ID,
        ])
            ->orderBy('id')
            ->get();
    }

    /**
     * Daftar pilihan gang.
     */
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
     * Menampilkan daftar warga dan data Ketua RT.
     */
    public function index()
    {
        $ketuaRT = Pengguna::with('role')
            ->where('role_id', self::ROLE_KETUA_RT_ID)
            ->first();

        $warga = Pengguna::with('role')
            ->whereIn('role_id', [
                self::ROLE_WARGA_ID,
                self::ROLE_WARGA_BIASA_ID,
            ])
            ->latest()
            ->paginate(10);

        return view(
            'ketua_rt.kelola_warga.index',
            compact('warga', 'ketuaRT')
        );
    }

    /**
     * Menampilkan form tambah warga.
     */
    public function create()
    {
        $roles = $this->daftarRoleWarga();
        $daftarGang = $this->daftarGang();

        return view(
            'ketua_rt.kelola_warga.create',
            compact('roles', 'daftarGang')
        );
    }

    /**
     * Menyimpan warga baru.
     */
    public function store(Request $request)
    {
        $roleIds = $this->daftarRoleWarga()
            ->pluck('id')
            ->toArray();

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
                Rule::in($roleIds),
            ],
        ], [
            'role_id.required' => 'Role wajib dipilih.',
            'role_id.in' => 'Role yang dipilih tidak valid.',
            'password.min' => 'Password minimal 8 karakter.',
        ]);

        // Akun baru secara default berstatus aktif.
        $validated['status'] = 'aktif';
        $validated['dinonaktifkan_pada'] = null;

        $warga = Pengguna::create($validated);

        LogAktivitas::create([
            'pengguna_id' => Auth::id(),
            'aktivitas' => 'Menambahkan warga',
            'deskripsi' => "Menambahkan akun warga {$warga->nama}.",
        ]);

        return redirect()
            ->route('ketua-rt.kelola-warga.index')
            ->with('success', 'Data warga berhasil ditambahkan.');
    }

    /**
     * Menampilkan detail akun Ketua RT atau warga.
     */
    public function show(Pengguna $pengguna)
    {
        $this->pastikanAkunDapatDikelola($pengguna);

        $pengguna->load('role');

        return view(
            'ketua_rt.kelola_warga.show',
            compact('pengguna')
        );
    }

    /**
     * Menampilkan form edit akun Ketua RT atau warga.
     */
    public function edit(Pengguna $pengguna)
    {
        $this->pastikanAkunDapatDikelola($pengguna);

        $isKetuaRT = (int) $pengguna->role_id === self::ROLE_KETUA_RT_ID;

        // Ketua RT hanya ditampilkan dengan role miliknya sendiri.
        $roles = $isKetuaRT
            ? Role::whereKey(self::ROLE_KETUA_RT_ID)->get()
            : $this->daftarRoleWarga();

        $daftarGang = $this->daftarGang();

        return view(
            'ketua_rt.kelola_warga.edit',
            compact(
                'pengguna',
                'roles',
                'daftarGang',
                'isKetuaRT'
            )
        );
    }

    /**
     * Memperbarui data akun Ketua RT atau warga.
     */
    public function update(Request $request, Pengguna $pengguna)
    {
        $this->pastikanAkunDapatDikelola($pengguna);

        $isKetuaRT = (int) $pengguna->role_id === self::ROLE_KETUA_RT_ID;

        // Aturan validasi data umum.
        $rules = [
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
        ];

        // Role hanya dapat diubah untuk akun Warga dan Warga Biasa.
        if (! $isKetuaRT) {
            $roleIds = $this->daftarRoleWarga()
                ->pluck('id')
                ->toArray();

            $rules['role_id'] = [
                'required',
                'integer',
                Rule::in($roleIds),
            ];
        }

        $validated = $request->validate($rules, [
            'role_id.required' => 'Role wajib dipilih.',
            'role_id.in' => 'Role yang dipilih tidak valid.',
            'password.min' => 'Password minimal 8 karakter.',
        ]);

        // Password tidak diubah jika dikosongkan.
        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        // Status dan waktu penonaktifan tidak diubah melalui form edit.
        $pengguna->update($validated);

        LogAktivitas::create([
            'pengguna_id' => Auth::id(),
            'aktivitas' => 'Memperbarui data warga',
            'deskripsi' => "Memperbarui data akun warga {$pengguna->nama}.",
        ]);

        return redirect()
            ->route('ketua-rt.kelola-warga.index')
            ->with('success', 'Data berhasil diperbarui.');
    }

    /**
     * Mengaktifkan atau menonaktifkan akun warga.
     */
    public function ubahStatus(Pengguna $pengguna)
    {
        // Ketua RT dan role lain tidak dapat diubah statusnya.
        $this->pastikanWarga($pengguna);

        if ($pengguna->status === 'aktif') {
            // Menonaktifkan akun dan mencatat waktunya.
            $pengguna->update([
                'status' => 'nonaktif',
                'dinonaktifkan_pada' => now(),
            ]);

            $pesan = 'Akun warga berhasil dinonaktifkan.';

            LogAktivitas::create([
                'pengguna_id' => Auth::id(),
                'aktivitas' => 'Menonaktifkan akun warga',
                'deskripsi' => "Menonaktifkan akun warga {$pengguna->nama}.",
            ]);
        } else {
            // Mengaktifkan kembali akun dan menghapus waktu penonaktifan.
            $pengguna->update([
                'status' => 'aktif',
                'dinonaktifkan_pada' => null,
            ]);

            $pesan = 'Akun warga berhasil diaktifkan.';

            LogAktivitas::create([
                'pengguna_id' => Auth::id(),
                'aktivitas' => 'Mengaktifkan akun warga',
                'deskripsi' => "Mengaktifkan akun warga {$pengguna->nama}.",
            ]);
        }

        return redirect()
            ->route('ketua-rt.kelola-warga.index')
            ->with('success', $pesan);
    }

    /**
     * Memastikan akun merupakan Ketua RT, Warga,
     * atau Warga Biasa.
     */
    private function pastikanAkunDapatDikelola(Pengguna $pengguna): void
    {
        abort_unless(
            in_array(
                (int) $pengguna->role_id,
                [
                    self::ROLE_KETUA_RT_ID,
                    self::ROLE_WARGA_ID,
                    self::ROLE_WARGA_BIASA_ID,
                ],
                true
            ),
            404
        );
    }

    /**
     * Memastikan akun merupakan Warga atau Warga Biasa.
     */
    private function pastikanWarga(Pengguna $pengguna): void
    {
        abort_unless(
            in_array(
                (int) $pengguna->role_id,
                [
                    self::ROLE_WARGA_ID,
                    self::ROLE_WARGA_BIASA_ID,
                ],
                true
            ),
            404
        );
    }
}
