<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Kegiatan;
use App\Models\KegiatanPanitia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KegiatanPanitiaController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Konstanta Role
    |--------------------------------------------------------------------------
    */

    private const ROLE_WARGA_ID = 2;

    private const JABATAN_KETUA_PELAKSANA = 'Ketua Pelaksana';

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
            'Anda tidak memiliki akses untuk mengelola panitia kegiatan.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Pastikan Panitia Milik Kegiatan
    |--------------------------------------------------------------------------
    */

    private function pastikanPanitiaMilikKegiatan(
        Kegiatan $kegiatan,
        KegiatanPanitia $panitia
    ): void {
        abort_unless(
            (int) $panitia->kegiatan_id === (int) $kegiatan->id,
            404
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Siapkan Jabatan
    |--------------------------------------------------------------------------
    */

    private function siapkanJabatan(Request $request): void
    {
        if ($request->input('jabatan') === '__lainnya__') {
            $request->merge([
                'jabatan' => trim(
                    (string) $request->input('jabatan_lainnya')
                ),
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Validasi Data Panitia
    |--------------------------------------------------------------------------
    */

    private function aturanValidasi(): array
    {
        return [
            'nama_panitia' => [
                'required',
                'string',
                'max:150',
            ],

            'jabatan' => [
                'required',
                'string',
                'max:100',
            ],

            'tugas' => [
                'nullable',
                'string',
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Simpan Panitia
    |--------------------------------------------------------------------------
    */

    public function store(Request $request, Kegiatan $kegiatan)
    {
        $this->pastikanBolehKelola();

        $this->siapkanJabatan($request);

        $validated = $request->validate(
            $this->aturanValidasi(),
            [
                'nama_panitia.required' => 'Nama panitia wajib diisi.',
                'jabatan.required' => 'Jabatan panitia wajib diisi.',
                'jabatan.max' => 'Jabatan maksimal 100 karakter.',
            ]
        );

        // Setiap kegiatan hanya boleh memiliki satu Ketua Pelaksana.
        if (
            $validated['jabatan'] === self::JABATAN_KETUA_PELAKSANA
            && $kegiatan->panitia()
                ->where('jabatan', self::JABATAN_KETUA_PELAKSANA)
                ->exists()
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'jabatan' => 'Kegiatan ini sudah memiliki Ketua Pelaksana.',
                ]);
        }

        $kegiatan->panitia()->create([
            'nama_panitia' => $validated['nama_panitia'],
            'jabatan' => $validated['jabatan'],
            'tugas' => $validated['tugas'] ?? null,
        ]);
        $kegiatan->update(['pengubah_id' => Auth::id()]);

        return redirect()
            ->route('kegiatan.show', $kegiatan->id)
            ->with('success', 'Panitia kegiatan berhasil ditambahkan.');
    }

    /*
    |--------------------------------------------------------------------------
    | Update Panitia
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Kegiatan $kegiatan,
        KegiatanPanitia $panitia
    ) {
        $this->pastikanBolehKelola();
        $this->pastikanPanitiaMilikKegiatan($kegiatan, $panitia);

        $this->siapkanJabatan($request);

        $validated = $request->validate(
            $this->aturanValidasi(),
            [
                'nama_panitia.required' => 'Nama panitia wajib diisi.',
                'jabatan.required' => 'Jabatan panitia wajib diisi.',
                'jabatan.max' => 'Jabatan maksimal 100 karakter.',
            ]
        );

        // Cegah jabatan Ketua Pelaksana diberikan kepada lebih dari satu orang.
        if (
            $validated['jabatan'] === self::JABATAN_KETUA_PELAKSANA
            && $kegiatan->panitia()
                ->where('jabatan', self::JABATAN_KETUA_PELAKSANA)
                ->where('id', '!=', $panitia->id)
                ->exists()
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'jabatan' => 'Kegiatan ini sudah memiliki Ketua Pelaksana.',
                ]);
        }

        // Ketua Pelaksana terakhir tidak boleh kehilangan jabatannya.
        if (
            $panitia->jabatan === self::JABATAN_KETUA_PELAKSANA
            && $validated['jabatan'] !== self::JABATAN_KETUA_PELAKSANA
            && $kegiatan->panitia()
                ->where('jabatan', self::JABATAN_KETUA_PELAKSANA)
                ->count() <= 1
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'jabatan' => 'Kegiatan harus memiliki minimal satu Ketua Pelaksana.',
                ]);
        }

        $panitia->update([
            'nama_panitia' => $validated['nama_panitia'],
            'jabatan' => $validated['jabatan'],
            'tugas' => $validated['tugas'] ?? null,
        ]);
        $kegiatan->update(['pengubah_id' => Auth::id()]);

        return redirect()
            ->route('kegiatan.show', $kegiatan->id)
            ->with('success', 'Data panitia berhasil diperbarui.');
    }

    /*
    |--------------------------------------------------------------------------
    | Hapus Panitia
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Kegiatan $kegiatan,
        KegiatanPanitia $panitia
    ) {
        $this->pastikanBolehKelola();
        $this->pastikanPanitiaMilikKegiatan($kegiatan, $panitia);

        // Ketua Pelaksana terakhir tidak boleh dihapus.
        if (
            $panitia->jabatan === self::JABATAN_KETUA_PELAKSANA
            && $kegiatan->panitia()
                ->where('jabatan', self::JABATAN_KETUA_PELAKSANA)
                ->count() <= 1
        ) {
            return back()->with(
                'error',
                'Ketua Pelaksana tidak dapat dihapus karena kegiatan harus memilikinya.'
            );
        }

        $panitia->delete();
        $kegiatan->update(['pengubah_id' => Auth::id()]);

        return redirect()
            ->route('kegiatan.show', $kegiatan->id)
            ->with('success', 'Panitia berhasil dihapus.');
    }
}
