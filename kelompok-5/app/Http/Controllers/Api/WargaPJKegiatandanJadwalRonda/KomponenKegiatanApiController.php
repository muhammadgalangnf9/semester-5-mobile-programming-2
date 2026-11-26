<?php

namespace App\Http\Controllers\Api\WargaPJKegiatandanJadwalRonda;

use App\Http\Controllers\Controller;
use App\Models\DokumentasiKegiatan;
use App\Models\Kegiatan;
use App\Models\KegiatanPanitia;
use App\Models\PesertaKegiatan;
use App\Models\RabKegiatan;
use App\Models\TransaksiKegiatan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KomponenKegiatanApiController extends Controller
{
    private const JABATAN_KETUA_PELAKSANA = 'Ketua Pelaksana';

    private const STATUS_KEHADIRAN = [
        'belum_dikonfirmasi',
        'hadir',
        'tidak_hadir',
        'izin',
        'sakit',
    ];

    private function pastikanKegiatanSesamaRole(
        Request $request,
        Kegiatan $kegiatan
    ): void {
        abort_unless(
            $kegiatan->pengguna()
                ->where('role_id', $request->user()->role_id)
                ->exists(),
            404
        );
    }

    private function pastikanKomponenMilikKegiatan(
        Kegiatan $kegiatan,
        Model $komponen
    ): void {
        abort_unless(
            (int) $komponen->kegiatan_id === (int) $kegiatan->id,
            404
        );
    }

    private function berhasil(string $pesan, Model $data, int $status = 200)
    {
        return response()->json([
            'berhasil' => true,
            'pesan' => $pesan,
            'data' => $data,
        ], $status);
    }

    private function gagal(string $pesan, int $status)
    {
        return response()->json([
            'berhasil' => false,
            'pesan' => $pesan,
        ], $status);
    }

    private function validasiPanitia(
        Request $request,
        Kegiatan $kegiatan,
        ?KegiatanPanitia $panitia = null
    ): array {
        $validated = $request->validate([
            'nama_panitia' => ['required', 'string', 'max:150'],
            'jabatan' => ['required', 'string', 'max:100'],
            'tugas' => ['nullable', 'string'],
        ]);

        if (
            $validated['jabatan'] === self::JABATAN_KETUA_PELAKSANA
            && $kegiatan->panitia()
                ->where('jabatan', self::JABATAN_KETUA_PELAKSANA)
                ->when(
                    $panitia,
                    fn ($query) => $query->where('id', '!=', $panitia->id)
                )
                ->exists()
        ) {
            abort(422, 'Kegiatan ini sudah memiliki Ketua Pelaksana.');
        }

        return $validated;
    }

    public function storePanitia(Request $request, Kegiatan $kegiatan)
    {
        $this->pastikanKegiatanSesamaRole($request, $kegiatan);
        $validated = $this->validasiPanitia($request, $kegiatan);

        $panitia = $kegiatan->panitia()->create($validated);
        $this->tandaiPengubah($request, $kegiatan);

        return $this->berhasil('Panitia berhasil ditambahkan.', $panitia, 201);
    }

    public function updatePanitia(
        Request $request,
        Kegiatan $kegiatan,
        KegiatanPanitia $panitia
    ) {
        $this->pastikanKegiatanSesamaRole($request, $kegiatan);
        $this->pastikanKomponenMilikKegiatan($kegiatan, $panitia);
        $validated = $this->validasiPanitia($request, $kegiatan, $panitia);

        if (
            $panitia->jabatan === self::JABATAN_KETUA_PELAKSANA
            && $validated['jabatan'] !== self::JABATAN_KETUA_PELAKSANA
            && $kegiatan->panitia()
                ->where('jabatan', self::JABATAN_KETUA_PELAKSANA)
                ->count() <= 1
        ) {
            return $this->gagal(
                'Kegiatan harus memiliki minimal satu Ketua Pelaksana.',
                422
            );
        }

        $panitia->update($validated);
        $this->tandaiPengubah($request, $kegiatan);

        return $this->berhasil('Panitia berhasil diperbarui.', $panitia);
    }

    public function destroyPanitia(
        Request $request,
        Kegiatan $kegiatan,
        KegiatanPanitia $panitia
    ) {
        $this->pastikanKegiatanSesamaRole($request, $kegiatan);
        $this->pastikanKomponenMilikKegiatan($kegiatan, $panitia);

        if (
            $panitia->jabatan === self::JABATAN_KETUA_PELAKSANA
            && $kegiatan->panitia()
                ->where('jabatan', self::JABATAN_KETUA_PELAKSANA)
                ->count() <= 1
        ) {
            return $this->gagal(
                'Ketua Pelaksana tidak dapat dihapus karena kegiatan harus memilikinya.',
                422
            );
        }

        $panitia->delete();
        $this->tandaiPengubah($request, $kegiatan);

        return response()->json([
            'berhasil' => true,
            'pesan' => 'Panitia berhasil dihapus.',
        ]);
    }

    private function tandaiPengubah(Request $request, Kegiatan $kegiatan): void
    {
        $kegiatan->update([
            'pengubah_id' => $request->user()->id,
        ]);
    }

    private function validasiRab(Request $request): array
    {
        return $request->validate([
            'nama_kebutuhan' => ['required', 'string', 'max:150'],
            'kategori_pengeluaran' => ['required', 'string', 'max:100'],
            'jumlah' => ['required', 'numeric', 'gt:0'],
            'satuan' => ['required', 'string', 'max:100'],
            'harga_satuan' => ['required', 'numeric', 'min:0'],
            'sumber_dana' => ['required', 'string', 'max:100'],
        ]);
    }

    private function dataRab(array $validated): array
    {
        $validated['total_biaya'] = round(
            (float) $validated['jumlah'] * (float) $validated['harga_satuan'],
            2
        );

        return $validated;
    }

    public function storeRab(Request $request, Kegiatan $kegiatan)
    {
        $this->pastikanKegiatanSesamaRole($request, $kegiatan);
        $rab = $kegiatan->rab()->create(
            $this->dataRab($this->validasiRab($request))
        );
        $this->tandaiPengubah($request, $kegiatan);

        return $this->berhasil('Item RAB berhasil ditambahkan.', $rab, 201);
    }

    public function updateRab(
        Request $request,
        Kegiatan $kegiatan,
        RabKegiatan $rab
    ) {
        $this->pastikanKegiatanSesamaRole($request, $kegiatan);
        $this->pastikanKomponenMilikKegiatan($kegiatan, $rab);
        $rab->update($this->dataRab($this->validasiRab($request)));
        $this->tandaiPengubah($request, $kegiatan);

        return $this->berhasil('Item RAB berhasil diperbarui.', $rab);
    }

    public function destroyRab(
        Request $request,
        Kegiatan $kegiatan,
        RabKegiatan $rab
    ) {
        $this->pastikanKegiatanSesamaRole($request, $kegiatan);
        $this->pastikanKomponenMilikKegiatan($kegiatan, $rab);
        $rab->delete();
        $this->tandaiPengubah($request, $kegiatan);

        return response()->json([
            'berhasil' => true,
            'pesan' => 'Item RAB berhasil dihapus.',
        ]);
    }

    private function validasiTransaksi(Request $request): array
    {
        return $request->validate([
            'jenis_transaksi' => [
                'required',
                Rule::in(['pemasukan', 'pengeluaran']),
            ],
            'kategori' => ['required', 'string', 'max:100'],
            'tanggal_transaksi' => ['required', 'date'],
            'nominal' => ['required', 'numeric', 'gt:0'],
            'keterangan' => ['nullable', 'string'],
            'link_bukti' => ['nullable', 'url', 'max:500'],
        ]);
    }

    public function storeTransaksi(Request $request, Kegiatan $kegiatan)
    {
        $this->pastikanKegiatanSesamaRole($request, $kegiatan);
        $transaksi = $kegiatan->transaksi()->create(
            $this->validasiTransaksi($request)
        );
        $this->tandaiPengubah($request, $kegiatan);

        return $this->berhasil(
            'Transaksi berhasil ditambahkan.',
            $transaksi,
            201
        );
    }

    public function updateTransaksi(
        Request $request,
        Kegiatan $kegiatan,
        TransaksiKegiatan $transaksi
    ) {
        $this->pastikanKegiatanSesamaRole($request, $kegiatan);
        $this->pastikanKomponenMilikKegiatan($kegiatan, $transaksi);
        $transaksi->update($this->validasiTransaksi($request));
        $this->tandaiPengubah($request, $kegiatan);

        return $this->berhasil('Transaksi berhasil diperbarui.', $transaksi);
    }

    public function destroyTransaksi(
        Request $request,
        Kegiatan $kegiatan,
        TransaksiKegiatan $transaksi
    ) {
        $this->pastikanKegiatanSesamaRole($request, $kegiatan);
        $this->pastikanKomponenMilikKegiatan($kegiatan, $transaksi);
        $transaksi->delete();
        $this->tandaiPengubah($request, $kegiatan);

        return response()->json([
            'berhasil' => true,
            'pesan' => 'Transaksi berhasil dihapus.',
        ]);
    }

    private function validasiPeserta(Request $request): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'gang' => ['required', 'string', 'max:100'],
            'status_kehadiran' => [
                'required',
                Rule::in(self::STATUS_KEHADIRAN),
            ],
            'keterangan' => ['nullable', 'string'],
        ]);
    }

    public function storePeserta(Request $request, Kegiatan $kegiatan)
    {
        $this->pastikanKegiatanSesamaRole($request, $kegiatan);
        $peserta = $kegiatan->peserta()->create(
            $this->validasiPeserta($request)
        );
        $this->tandaiPengubah($request, $kegiatan);

        return $this->berhasil(
            'Peserta kegiatan berhasil ditambahkan.',
            $peserta,
            201
        );
    }

    public function updatePeserta(
        Request $request,
        Kegiatan $kegiatan,
        PesertaKegiatan $peserta
    ) {
        $this->pastikanKegiatanSesamaRole($request, $kegiatan);
        $this->pastikanKomponenMilikKegiatan($kegiatan, $peserta);
        $peserta->update($this->validasiPeserta($request));
        $this->tandaiPengubah($request, $kegiatan);

        return $this->berhasil('Peserta kegiatan berhasil diperbarui.', $peserta);
    }

    public function destroyPeserta(
        Request $request,
        Kegiatan $kegiatan,
        PesertaKegiatan $peserta
    ) {
        $this->pastikanKegiatanSesamaRole($request, $kegiatan);
        $this->pastikanKomponenMilikKegiatan($kegiatan, $peserta);
        $peserta->delete();
        $this->tandaiPengubah($request, $kegiatan);

        return response()->json([
            'berhasil' => true,
            'pesan' => 'Peserta kegiatan berhasil dihapus.',
        ]);
    }

    private function validasiDokumentasi(Request $request): array
    {
        return $request->validate([
            'nama_dokumentasi' => ['required', 'string', 'max:150'],
            'jenis' => ['required', Rule::in(['foto', 'video'])],
            'link_drive' => ['required', 'url', 'max:500'],
        ]);
    }

    public function storeDokumentasi(Request $request, Kegiatan $kegiatan)
    {
        $this->pastikanKegiatanSesamaRole($request, $kegiatan);
        $dokumentasi = $kegiatan->dokumentasi()->create(
            $this->validasiDokumentasi($request)
        );
        $this->tandaiPengubah($request, $kegiatan);

        return $this->berhasil(
            'Dokumentasi berhasil ditambahkan.',
            $dokumentasi,
            201
        );
    }

    public function updateDokumentasi(
        Request $request,
        Kegiatan $kegiatan,
        DokumentasiKegiatan $dokumentasi
    ) {
        $this->pastikanKegiatanSesamaRole($request, $kegiatan);
        $this->pastikanKomponenMilikKegiatan($kegiatan, $dokumentasi);
        $dokumentasi->update($this->validasiDokumentasi($request));
        $this->tandaiPengubah($request, $kegiatan);

        return $this->berhasil(
            'Dokumentasi berhasil diperbarui.',
            $dokumentasi
        );
    }

    public function destroyDokumentasi(
        Request $request,
        Kegiatan $kegiatan,
        DokumentasiKegiatan $dokumentasi
    ) {
        $this->pastikanKegiatanSesamaRole($request, $kegiatan);
        $this->pastikanKomponenMilikKegiatan($kegiatan, $dokumentasi);
        $dokumentasi->delete();
        $this->tandaiPengubah($request, $kegiatan);

        return response()->json([
            'berhasil' => true,
            'pesan' => 'Dokumentasi berhasil dihapus.',
        ]);
    }
}
