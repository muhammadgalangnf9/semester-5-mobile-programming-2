<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Kegiatan;
use App\Models\TransaksiKegiatan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TransaksiKegiatanController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Konstanta Role
    |--------------------------------------------------------------------------
    */

    private const ROLE_WARGA_ID = 2;

    private const JENIS_TRANSAKSI = [
        'pemasukan',
        'pengeluaran',
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
            'Anda tidak memiliki akses untuk mengelola transaksi kegiatan.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Pastikan Transaksi Milik Kegiatan
    |--------------------------------------------------------------------------
    */

    private function pastikanTransaksiMilikKegiatan(
        Kegiatan $kegiatan,
        TransaksiKegiatan $transaksi
    ): void {
        abort_unless(
            (int) $transaksi->kegiatan_id === (int) $kegiatan->id,
            404
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Siapkan Kategori
    |--------------------------------------------------------------------------
    */

    private function siapkanKategori(Request $request): void
    {
        if ($request->input('kategori') === '__lainnya__') {
            $request->merge([
                'kategori' => trim(
                    (string) $request->input('kategori_lainnya')
                ),
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Aturan Validasi
    |--------------------------------------------------------------------------
    */

    private function aturanValidasi(): array
    {
        return [
            'jenis_transaksi' => [
                'required',
                Rule::in(self::JENIS_TRANSAKSI),
            ],

            'kategori' => [
                'required',
                'string',
                'max:100',
            ],

            'tanggal_transaksi' => [
                'required',
                'date',
            ],

            'nominal' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'keterangan' => [
                'nullable',
                'string',
            ],

            'link_bukti' => [
                'nullable',
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
            'jenis_transaksi.required' => 'Jenis transaksi wajib dipilih.',
            'jenis_transaksi.in' => 'Jenis transaksi tidak valid.',

            'kategori.required' => 'Kategori transaksi wajib diisi.',
            'kategori.max' => 'Kategori transaksi maksimal 100 karakter.',

            'tanggal_transaksi.required' => 'Tanggal transaksi wajib diisi.',
            'tanggal_transaksi.date' => 'Tanggal transaksi tidak valid.',

            'nominal.required' => 'Nominal transaksi wajib diisi.',
            'nominal.numeric' => 'Nominal harus berupa angka.',
            'nominal.gt' => 'Nominal harus lebih dari 0.',

            'link_bukti.url' => 'Link bukti harus berupa URL yang valid.',
            'link_bukti.max' => 'Link bukti maksimal 500 karakter.',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Simpan Transaksi
    |--------------------------------------------------------------------------
    */

    public function store(Request $request, Kegiatan $kegiatan)
    {
        $this->pastikanBolehKelola();

        $this->siapkanKategori($request);

        $validated = $request->validate(
            $this->aturanValidasi(),
            $this->pesanValidasi()
        );

        $kegiatan->transaksi()->create([
            'jenis_transaksi' => $validated['jenis_transaksi'],
            'kategori' => $validated['kategori'],
            'tanggal_transaksi' => $validated['tanggal_transaksi'],
            'nominal' => $validated['nominal'],
            'keterangan' => $validated['keterangan'] ?? null,
            'link_bukti' => $validated['link_bukti'] ?? null,
        ]);

        return redirect()
            ->route('kegiatan.show', $kegiatan->id)
            ->with('success', 'Transaksi kegiatan berhasil ditambahkan.');
    }

    /*
    |--------------------------------------------------------------------------
    | Update Transaksi
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Kegiatan $kegiatan,
        TransaksiKegiatan $transaksi
    ) {
        $this->pastikanBolehKelola();
        $this->pastikanTransaksiMilikKegiatan($kegiatan, $transaksi);

        $this->siapkanKategori($request);

        $validated = $request->validate(
            $this->aturanValidasi(),
            $this->pesanValidasi()
        );

        $transaksi->update([
            'jenis_transaksi' => $validated['jenis_transaksi'],
            'kategori' => $validated['kategori'],
            'tanggal_transaksi' => $validated['tanggal_transaksi'],
            'nominal' => $validated['nominal'],
            'keterangan' => $validated['keterangan'] ?? null,
            'link_bukti' => $validated['link_bukti'] ?? null,
        ]);

        return redirect()
            ->route('kegiatan.show', $kegiatan->id)
            ->with('success', 'Transaksi kegiatan berhasil diperbarui.');
    }

    /*
    |--------------------------------------------------------------------------
    | Hapus Transaksi
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Kegiatan $kegiatan,
        TransaksiKegiatan $transaksi
    ) {
        $this->pastikanBolehKelola();
        $this->pastikanTransaksiMilikKegiatan($kegiatan, $transaksi);

        $transaksi->delete();

        return redirect()
            ->route('kegiatan.show', $kegiatan->id)
            ->with('success', 'Transaksi kegiatan berhasil dihapus.');
    }
}
