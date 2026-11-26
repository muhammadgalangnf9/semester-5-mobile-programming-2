<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Kegiatan;
use App\Models\RabKegiatan;
use Illuminate\Http\Request;

class RabKegiatanController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Konstanta Role
    |--------------------------------------------------------------------------
    */

    private const ROLE_WARGA_ID = 2;

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
            'Anda tidak memiliki akses untuk mengelola RAB kegiatan.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Pastikan RAB Milik Kegiatan
    |--------------------------------------------------------------------------
    */

    private function pastikanRabMilikKegiatan(
        Kegiatan $kegiatan,
        RabKegiatan $rab
    ): void {
        abort_unless(
            (int) $rab->kegiatan_id === (int) $kegiatan->id,
            404
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Siapkan Kategori dan Satuan
    |--------------------------------------------------------------------------
    */

    private function siapkanData(Request $request): void
    {
        $data = [];

        if ($request->input('kategori_pengeluaran') === '__lainnya__') {
            $data['kategori_pengeluaran'] = trim(
                (string) $request->input('kategori_pengeluaran_lainnya')
            );
        }

        if ($request->input('satuan') === '__lainnya__') {
            $data['satuan'] = trim(
                (string) $request->input('satuan_lainnya')
            );
        }

        if (!empty($data)) {
            $request->merge($data);
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
            'nama_kebutuhan' => [
                'required',
                'string',
                'max:150',
            ],

            'kategori_pengeluaran' => [
                'required',
                'string',
                'max:100',
            ],

            'jumlah' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'satuan' => [
                'required',
                'string',
                'max:100',
            ],

            'harga_satuan' => [
                'required',
                'numeric',
                'min:0',
            ],

            'sumber_dana' => [
                'required',
                'string',
                'max:100',
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
            'nama_kebutuhan.required' => 'Nama kebutuhan wajib diisi.',
            'nama_kebutuhan.max' => 'Nama kebutuhan maksimal 150 karakter.',

            'kategori_pengeluaran.required' => 'Kategori pengeluaran wajib diisi.',
            'kategori_pengeluaran.max' => 'Kategori pengeluaran maksimal 100 karakter.',

            'jumlah.required' => 'Jumlah kebutuhan wajib diisi.',
            'jumlah.numeric' => 'Jumlah harus berupa angka.',
            'jumlah.gt' => 'Jumlah harus lebih dari 0.',

            'satuan.required' => 'Satuan wajib dipilih atau diisi.',
            'satuan.max' => 'Satuan maksimal 100 karakter.',

            'harga_satuan.required' => 'Harga satuan wajib diisi.',
            'harga_satuan.numeric' => 'Harga satuan harus berupa angka.',
            'harga_satuan.min' => 'Harga satuan tidak boleh negatif.',

            'sumber_dana.required' => 'Sumber dana wajib diisi.',
            'sumber_dana.max' => 'Sumber dana maksimal 100 karakter.',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Hitung Total Biaya
    |--------------------------------------------------------------------------
    */

    private function hitungTotalBiaya(
        float $jumlah,
        float $hargaSatuan
    ): float {
        return round($jumlah * $hargaSatuan, 2);
    }

    /*
    |--------------------------------------------------------------------------
    | Simpan RAB
    |--------------------------------------------------------------------------
    */

    public function store(Request $request, Kegiatan $kegiatan)
    {
        $this->pastikanBolehKelola();

        $this->siapkanData($request);

        $validated = $request->validate(
            $this->aturanValidasi(),
            $this->pesanValidasi()
        );

        $totalBiaya = $this->hitungTotalBiaya(
            (float) $validated['jumlah'],
            (float) $validated['harga_satuan']
        );

        $kegiatan->rab()->create([
            'nama_kebutuhan' => $validated['nama_kebutuhan'],
            'kategori_pengeluaran' => $validated['kategori_pengeluaran'],
            'jumlah' => $validated['jumlah'],
            'satuan' => $validated['satuan'],
            'harga_satuan' => $validated['harga_satuan'],
            'total_biaya' => $totalBiaya,
            'sumber_dana' => $validated['sumber_dana'],
        ]);
        $kegiatan->update(['pengubah_id' => auth()->id()]);

        return redirect()
            ->route('kegiatan.show', $kegiatan->id)
            ->with('success', 'Item RAB berhasil ditambahkan.');
    }

    /*
    |--------------------------------------------------------------------------
    | Update RAB
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Kegiatan $kegiatan,
        RabKegiatan $rab
    ) {
        $this->pastikanBolehKelola();
        $this->pastikanRabMilikKegiatan($kegiatan, $rab);

        $this->siapkanData($request);

        $validated = $request->validate(
            $this->aturanValidasi(),
            $this->pesanValidasi()
        );

        $totalBiaya = $this->hitungTotalBiaya(
            (float) $validated['jumlah'],
            (float) $validated['harga_satuan']
        );

        $rab->update([
            'nama_kebutuhan' => $validated['nama_kebutuhan'],
            'kategori_pengeluaran' => $validated['kategori_pengeluaran'],
            'jumlah' => $validated['jumlah'],
            'satuan' => $validated['satuan'],
            'harga_satuan' => $validated['harga_satuan'],
            'total_biaya' => $totalBiaya,
            'sumber_dana' => $validated['sumber_dana'],
        ]);
        $kegiatan->update(['pengubah_id' => auth()->id()]);

        return redirect()
            ->route('kegiatan.show', $kegiatan->id)
            ->with('success', 'Item RAB berhasil diperbarui.');
    }

    /*
    |--------------------------------------------------------------------------
    | Hapus RAB
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Kegiatan $kegiatan,
        RabKegiatan $rab
    ) {
        $this->pastikanBolehKelola();
        $this->pastikanRabMilikKegiatan($kegiatan, $rab);

        $rab->delete();
        $kegiatan->update(['pengubah_id' => auth()->id()]);

        return redirect()
            ->route('kegiatan.show', $kegiatan->id)
            ->with('success', 'Item RAB berhasil dihapus.');
    }
}
