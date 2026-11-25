<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Kegiatan;
use App\Models\DokumentasiKegiatan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DokumentasiKegiatanController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Konstanta Role
    |--------------------------------------------------------------------------
    */

    private const ROLE_WARGA_ID = 2;

    private const JENIS_DOKUMENTASI = [
        'foto',
        'video',
    ];

    /*
    |--------------------------------------------------------------------------
    | Hak Akses
    |--------------------------------------------------------------------------
    */

    private function bolehKelola(): bool
    {
        $pengguna = auth()->user();

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
            'Anda tidak memiliki akses untuk mengelola dokumentasi kegiatan.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Pastikan Dokumentasi Milik Kegiatan
    |--------------------------------------------------------------------------
    */

    private function pastikanDokumentasiMilikKegiatan(
        Kegiatan $kegiatan,
        DokumentasiKegiatan $dokumentasi
    ): void {
        abort_unless(
            (int) $dokumentasi->kegiatan_id === (int) $kegiatan->id,
            404
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Aturan Validasi
    |--------------------------------------------------------------------------
    */

    private function aturanValidasi(): array
    {
        return [
            'nama_dokumentasi' => [
                'required',
                'string',
                'max:150',
            ],

            'jenis' => [
                'required',
                Rule::in(self::JENIS_DOKUMENTASI),
            ],

            'link_drive' => [
                'required',
                'url',
                'max:500',
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Pesan Validasi
    |--------------------------------------------------------------------------
    */

    private function pesanValidasi(): array
    {
        return [
            'nama_dokumentasi.required' => 'Nama dokumentasi wajib diisi.',
            'nama_dokumentasi.max' => 'Nama dokumentasi maksimal 150 karakter.',

            'jenis.required' => 'Jenis dokumentasi wajib dipilih.',
            'jenis.in' => 'Jenis dokumentasi hanya boleh berupa foto atau video.',

            'link_drive.required' => 'Link Google Drive wajib diisi.',
            'link_drive.url' => 'Link dokumentasi harus berupa URL yang valid.',
            'link_drive.max' => 'Link dokumentasi maksimal 500 karakter.',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Simpan Dokumentasi
    |--------------------------------------------------------------------------
    */

    public function store(Request $request, Kegiatan $kegiatan)
    {
        $this->pastikanBolehKelola();

        $validated = $request->validate(
            $this->aturanValidasi(),
            $this->pesanValidasi()
        );

        $kegiatan->dokumentasi()->create([
            'nama_dokumentasi' => $validated['nama_dokumentasi'],
            'jenis' => $validated['jenis'],
            'link_drive' => $validated['link_drive'],
        ]);

        return redirect()
            ->route('kegiatan.show', $kegiatan->id)
            ->with('success', 'Dokumentasi kegiatan berhasil ditambahkan.');
    }

    /*
    |--------------------------------------------------------------------------
    | Update Dokumentasi
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Kegiatan $kegiatan,
        DokumentasiKegiatan $dokumentasi
    ) {
        $this->pastikanBolehKelola();
        $this->pastikanDokumentasiMilikKegiatan($kegiatan, $dokumentasi);

        $validated = $request->validate(
            $this->aturanValidasi(),
            $this->pesanValidasi()
        );

        $dokumentasi->update([
            'nama_dokumentasi' => $validated['nama_dokumentasi'],
            'jenis' => $validated['jenis'],
            'link_drive' => $validated['link_drive'],
        ]);

        return redirect()
            ->route('kegiatan.show', $kegiatan->id)
            ->with('success', 'Dokumentasi kegiatan berhasil diperbarui.');
    }

    /*
    |--------------------------------------------------------------------------
    | Hapus Dokumentasi
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Kegiatan $kegiatan,
        DokumentasiKegiatan $dokumentasi
    ) {
        $this->pastikanBolehKelola();
        $this->pastikanDokumentasiMilikKegiatan($kegiatan, $dokumentasi);

        $dokumentasi->delete();

        return redirect()
            ->route('kegiatan.show', $kegiatan->id)
            ->with('success', 'Dokumentasi kegiatan berhasil dihapus.');
    }
}
