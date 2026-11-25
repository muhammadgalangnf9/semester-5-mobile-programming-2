<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Kegiatan;
use App\Models\PesertaKegiatan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PesertaKegiatanController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Konstanta Role
    |--------------------------------------------------------------------------
    */

    private const ROLE_WARGA_ID = 2;

    private const STATUS_KEHADIRAN = [
        'belum_dikonfirmasi',
        'hadir',
        'tidak_hadir',
        'izin',
        'sakit',
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
            'Anda tidak memiliki akses untuk mengelola peserta kegiatan.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Pastikan Peserta Milik Kegiatan
    |--------------------------------------------------------------------------
    */

    private function pastikanPesertaMilikKegiatan(
        Kegiatan $kegiatan,
        PesertaKegiatan $peserta
    ): void {
        abort_unless(
            (int) $peserta->kegiatan_id === (int) $kegiatan->id,
            404
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Aturan Validasi Data Peserta
    |--------------------------------------------------------------------------
    */

    private function aturanValidasi(): array
    {
        return [
            'nama' => [
                'required',
                'string',
                'max:150',
            ],

            'gang' => [
                'required',
                'string',
                'max:100',
            ],

            'status_kehadiran' => [
                'required',
                Rule::in(self::STATUS_KEHADIRAN),
            ],

            'keterangan' => [
                'nullable',
                'string',
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
            'nama.required' => 'Nama peserta wajib diisi.',
            'nama.max' => 'Nama peserta maksimal 150 karakter.',

            'gang.required' => 'Gang peserta wajib dipilih.',
            'gang.max' => 'Nama gang maksimal 100 karakter.',

            'status_kehadiran.required' => 'Status kehadiran wajib dipilih.',
            'status_kehadiran.in' => 'Status kehadiran tidak valid.',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Simpan Peserta
    |--------------------------------------------------------------------------
    */

    public function store(Request $request, Kegiatan $kegiatan)
    {
        $this->pastikanBolehKelola();

        $validated = $request->validate(
            $this->aturanValidasi(),
            $this->pesanValidasi()
        );

        $kegiatan->peserta()->create([
            'nama' => $validated['nama'],
            'gang' => $validated['gang'],
            'status_kehadiran' => $validated['status_kehadiran'],
            'keterangan' => $validated['keterangan'] ?? null,
        ]);

        return redirect()
            ->route('kegiatan.show', $kegiatan->id)
            ->with('success', 'Peserta kegiatan berhasil ditambahkan.');
    }

    /*
    |--------------------------------------------------------------------------
    | Update Peserta
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Kegiatan $kegiatan,
        PesertaKegiatan $peserta
    ) {
        $this->pastikanBolehKelola();
        $this->pastikanPesertaMilikKegiatan($kegiatan, $peserta);

        $validated = $request->validate(
            $this->aturanValidasi(),
            $this->pesanValidasi()
        );

        $peserta->update([
            'nama' => $validated['nama'],
            'gang' => $validated['gang'],
            'status_kehadiran' => $validated['status_kehadiran'],
            'keterangan' => $validated['keterangan'] ?? null,
        ]);

        return redirect()
            ->route('kegiatan.show', $kegiatan->id)
            ->with('success', 'Data peserta berhasil diperbarui.');
    }

    /*
    |--------------------------------------------------------------------------
    | Hapus Peserta
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Kegiatan $kegiatan,
        PesertaKegiatan $peserta
    ) {
        $this->pastikanBolehKelola();
        $this->pastikanPesertaMilikKegiatan($kegiatan, $peserta);

        $peserta->delete();

        return redirect()
            ->route('kegiatan.show', $kegiatan->id)
            ->with('success', 'Peserta kegiatan berhasil dihapus.');
    }

    /*
    |--------------------------------------------------------------------------
    | Update Status Kehadiran
    |--------------------------------------------------------------------------
    */

    public function ubahKehadiran(
        Request $request,
        Kegiatan $kegiatan,
        PesertaKegiatan $peserta
    ) {
        $this->pastikanBolehKelola();
        $this->pastikanPesertaMilikKegiatan($kegiatan, $peserta);

        $validated = $request->validate([
            'status_kehadiran' => [
                'required',
                Rule::in(self::STATUS_KEHADIRAN),
            ],
        ], [
            'status_kehadiran.required' => 'Status kehadiran wajib dipilih.',
            'status_kehadiran.in' => 'Status kehadiran tidak valid.',
        ]);

        $peserta->update([
            'status_kehadiran' => $validated['status_kehadiran'],
        ]);

        return redirect()
            ->route('kegiatan.show', $kegiatan->id)
            ->with('success', 'Status kehadiran peserta berhasil diperbarui.');
    }
}
