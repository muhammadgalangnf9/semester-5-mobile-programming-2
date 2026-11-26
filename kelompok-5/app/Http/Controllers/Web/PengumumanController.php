<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Pengumuman;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PengumumanController extends Controller
{
    /**
     * ID role bawaan sistem.
     */
    private const ROLE_KETUA_RT_ID = 1;
    private const ROLE_WARGA_ID = 2;
    private const ROLE_WARGA_BIASA_ID = 3;

    /**
     * Memeriksa apakah pengguna memiliki hak kelola pengumuman.
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
     * Memastikan pengguna memiliki hak kelola pengumuman.
     */
    private function pastikanBolehKelola(): void
    {
        abort_unless(
            $this->bolehKelola(),
            403,
            'Anda tidak memiliki akses untuk mengelola pengumuman.'
        );
    }

    private function pastikanKetuaRT(): void
    {
        abort_unless(
            (int) Auth::user()->role_id === self::ROLE_KETUA_RT_ID,
            403,
            'Hanya Ketua RT yang dapat menyetujui pengumuman.'
        );
    }

    /**
     * Memastikan pengguna boleh melihat pengumuman.
     */
    private function pastikanBolehMelihat(Pengumuman $pengumuman): void
    {
        $pengguna = Auth::user();
        $roleId = (int) $pengguna->role_id;

        $bolehMelihat = in_array($roleId, [
            self::ROLE_KETUA_RT_ID,
            self::ROLE_WARGA_ID,
            self::ROLE_WARGA_BIASA_ID,
        ], true);

        abort_unless(
            $bolehMelihat,
            403,
            'Anda tidak memiliki akses ke pengumuman.'
        );

        // Pengguna yang tidak memiliki hak kelola
        // hanya boleh melihat pengumuman yang sudah terbit.
        if (!$this->bolehKelola()) {
            $roleId = (int) $pengguna->role_id;
            $statusDiizinkan = $roleId === self::ROLE_KETUA_RT_ID
                ? in_array($pengumuman->status, ['menunggu_persetujuan', 'terbit'], true)
                : $pengumuman->status === 'terbit';

            abort_unless(
                $statusDiizinkan,
                403,
                'Pengumuman tidak tersedia.'
            );
        }
    }

    /**
     * Menampilkan daftar pengumuman.
     */
    public function index(Request $request)
    {
        $pengguna = Auth::user();

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

        $query = Pengumuman::with(['pengguna', 'disetujuiOleh'])
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
            ->latest();

        // Ketua RT juga perlu melihat pengumuman yang menunggu persetujuan.
        if (!$this->bolehKelola()) {
            abort_unless(
                in_array((int) $pengguna->role_id, [
                    self::ROLE_KETUA_RT_ID,
                    self::ROLE_WARGA_ID,
                    self::ROLE_WARGA_BIASA_ID,
                ], true),
                403,
                'Anda tidak memiliki akses ke pengumuman.'
            );

            if ((int) $pengguna->role_id === self::ROLE_KETUA_RT_ID) {
                $query->whereIn('status', ['menunggu_persetujuan', 'terbit'])
                    ->orderByRaw("CASE WHEN status = 'menunggu_persetujuan' THEN 0 ELSE 1 END");
            } else {
                $query->where('status', 'terbit');
            }
        }

        $pengumuman = $query->paginate(10)->withQueryString();

        return view('pengumuman.index', compact('pengumuman'));
    }

    /**
     * Menampilkan form tambah pengumuman.
     */
    public function create()
    {
        $this->pastikanBolehKelola();

        return view('pengumuman.create');
    }

    /**
     * Menyimpan pengumuman baru.
     */
    public function store(Request $request)
    {
        $this->pastikanBolehKelola();

        $validated = $request->validate([
            'judul' => [
                'required',
                'string',
                'max:150',
            ],

            'isi' => [
                'required',
                'string',
            ],

        ], [
            'judul.required' => 'Judul pengumuman wajib diisi.',
            'judul.max' => 'Judul pengumuman maksimal 150 karakter.',
            'isi.required' => 'Isi pengumuman wajib diisi.',
        ]);

        $validated['pengguna_id'] = Auth::id();
        $validated['pengubah_id'] = Auth::id();
        $validated['status'] = 'draft';
        $validated['diterbitkan_pada'] = null;
        $validated['dinonaktifkan_pada'] = null;

        $data = Pengumuman::create($validated);

        LogAktivitas::create([
            'pengguna_id' => Auth::id(),
            'aktivitas' => 'Menambahkan pengumuman',
            'deskripsi' => "Menambahkan pengumuman {$data->judul}.",
        ]);

        return redirect()
            ->route('pengumuman.index')
            ->with('success', 'Pengumuman berhasil ditambahkan.');
    }

    /**
     * Menampilkan detail pengumuman.
     */
    public function show(Pengumuman $pengumuman)
    {
        $this->pastikanBolehMelihat($pengumuman);

        $pengumuman->load(['pengguna', 'pengubah', 'disetujuiOleh']);

        return view('pengumuman.show', compact('pengumuman'));
    }

    /**
     * Menampilkan form edit pengumuman.
     */
    public function edit(Pengumuman $pengumuman)
    {
        $this->pastikanBolehKelola();

        return view('pengumuman.edit', compact('pengumuman'));
    }

    /**
     * Memperbarui data pengumuman.
     */
    public function update(Request $request, Pengumuman $pengumuman)
    {
        $this->pastikanBolehKelola();

        $validated = $request->validate([
            'judul' => [
                'required',
                'string',
                'max:150',
            ],

            'isi' => [
                'required',
                'string',
            ],

        ], [
            'judul.required' => 'Judul pengumuman wajib diisi.',
            'judul.max' => 'Judul pengumuman maksimal 150 karakter.',
            'isi.required' => 'Isi pengumuman wajib diisi.',
        ]);

        $statusLama = $pengumuman->status;

        $judulLama = $pengumuman->judul;

        $pengumuman->judul = $validated['judul'];
        $pengumuman->isi = $validated['isi'];
        $pengumuman->pengubah_id = Auth::id();
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

        LogAktivitas::create([
            'pengguna_id' => Auth::id(),
            'aktivitas' => 'Memperbarui pengumuman',
            'deskripsi' => "Memperbarui pengumuman {$judulLama}.",
        ]);

        return redirect()
            ->route('pengumuman.index')
            ->with('success', 'Pengumuman berhasil diperbarui.');
    }

    /**
     * Mengubah status pengumuman.
     */
    public function ubahStatus(Request $request, Pengumuman $pengumuman)
    {
        $this->pastikanBolehKelola();

        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in(['draft', 'nonaktif']),
            ],
        ], [
            'status.required' => 'Status pengumuman wajib dipilih.',
            'status.in' => 'Status hanya dapat diubah menjadi draft atau nonaktif.',
        ]);

        $statusLama = $pengumuman->status;
        $statusBaru = $validated['status'];

        if ($statusBaru === 'nonaktif') {
            if ($statusLama !== 'nonaktif') {
                $pengumuman->dinonaktifkan_pada = now();
            }
        } else {
            $pengumuman->dinonaktifkan_pada = null;
            $pengumuman->diterbitkan_pada = null;
            $pengumuman->disetujui_oleh = null;
            $pengumuman->catatan_persetujuan = null;
        }

        $pengumuman->status = $statusBaru;
        $pengumuman->pengubah_id = Auth::id();
        $pengumuman->save();

        LogAktivitas::create([
            'pengguna_id' => Auth::id(),
            'aktivitas' => 'Mengubah status pengumuman',
            'deskripsi' => "Mengubah status pengumuman {$pengumuman->judul} dari {$statusLama} menjadi {$statusBaru}.",
        ]);

        return redirect()
            ->route('pengumuman.index')
            ->with('success', 'Status pengumuman berhasil diperbarui.');
    }

    public function ajukan(Pengumuman $pengumuman)
    {
        $this->pastikanBolehKelola();
        abort_unless(
            in_array($pengumuman->status, ['draft', 'ditolak'], true),
            422,
            'Pengumuman tidak dapat diajukan dengan status saat ini.'
        );

        $pengumuman->update([
            'status' => 'menunggu_persetujuan',
            'pengubah_id' => Auth::id(),
            'diterbitkan_pada' => null,
            'dinonaktifkan_pada' => null,
            'disetujui_oleh' => null,
            'catatan_persetujuan' => null,
        ]);

        LogAktivitas::create([
            'pengguna_id' => Auth::id(),
            'aktivitas' => 'Mengajukan pengumuman',
            'deskripsi' => "Mengajukan pengumuman {$pengumuman->judul} untuk persetujuan.",
        ]);

        return redirect()
            ->route('pengumuman.index')
            ->with('success', 'Pengumuman diajukan untuk persetujuan Ketua RT.');
    }

    public function setujui(Request $request, Pengumuman $pengumuman)
    {
        $this->pastikanKetuaRT();
        abort_unless($pengumuman->status === 'menunggu_persetujuan', 404);
        $validated = $request->validate([
            'catatan_persetujuan' => ['nullable', 'string'],
        ]);

        $pengumuman->update([
            'status' => 'terbit',
            'pengubah_id' => Auth::id(),
            'disetujui_oleh' => Auth::id(),
            'catatan_persetujuan' => $validated['catatan_persetujuan'] ?? null,
            'diterbitkan_pada' => now(),
            'dinonaktifkan_pada' => null,
        ]);

        LogAktivitas::create([
            'pengguna_id' => Auth::id(),
            'aktivitas' => 'Menyetujui pengumuman',
            'deskripsi' => "Menyetujui pengumuman {$pengumuman->judul}.",
        ]);

        return redirect()
            ->route('pengumuman.show', $pengumuman)
            ->with('success', 'Pengumuman disetujui dan diterbitkan.');
    }

    public function tolak(Request $request, Pengumuman $pengumuman)
    {
        $this->pastikanKetuaRT();
        abort_unless($pengumuman->status === 'menunggu_persetujuan', 404);
        $validated = $request->validate([
            'catatan_persetujuan' => ['required', 'string'],
        ]);

        $pengumuman->update([
            'status' => 'ditolak',
            'pengubah_id' => Auth::id(),
            'disetujui_oleh' => null,
            'catatan_persetujuan' => $validated['catatan_persetujuan'],
            'diterbitkan_pada' => null,
        ]);

        LogAktivitas::create([
            'pengguna_id' => Auth::id(),
            'aktivitas' => 'Menolak pengumuman',
            'deskripsi' => "Menolak pengumuman {$pengumuman->judul}.",
        ]);

        return redirect()
            ->route('pengumuman.show', $pengumuman)
            ->with('success', 'Pengumuman berhasil ditolak.');
    }
}
