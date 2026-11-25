<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kegiatan extends Model
{
    protected $table = 'kegiatan';

    protected $fillable = [
        'dibuat_oleh',
        'nama_kegiatan',
        'deskripsi',
        'jenis_kegiatan',
        'tanggal_mulai',
        'tanggal_selesai',
        'lokasi',
        'link_poster',
        'status',
        'diterbitkan_pada',
        'disetujui_oleh',
        'catatan_persetujuan',
        'dinonaktifkan_pada',
    ];

    protected $casts = [
        'tanggal_mulai' => 'datetime',
        'tanggal_selesai' => 'datetime',
        'diterbitkan_pada' => 'datetime',
        'dinonaktifkan_pada' => 'datetime',
    ];

    /**
     * Pengguna yang membuat kegiatan.
     */
    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'dibuat_oleh');
    }

    /**
     * Ketua RT yang menyetujui kegiatan.
     */
    public function disetujuiOleh()
    {
        return $this->belongsTo(Pengguna::class, 'disetujui_oleh');
    }

    /**
     * Data kepanitiaan kegiatan.
     */
    public function panitia()
    {
        return $this->hasMany(KegiatanPanitia::class, 'kegiatan_id');
    }

    /**
     * Data RAB kegiatan.
     */
    public function rab()
    {
        return $this->hasMany(RabKegiatan::class, 'kegiatan_id');
    }

    /**
     * Transaksi pemasukan dan pengeluaran.
     */
    public function transaksi()
    {
        return $this->hasMany(TransaksiKegiatan::class, 'kegiatan_id');
    }

    /**
     * Daftar peserta kegiatan.
     */
    public function peserta()
    {
        return $this->hasMany(PesertaKegiatan::class, 'kegiatan_id');
    }

    /**
     * Dokumentasi kegiatan.
     */
    public function dokumentasi()
    {
        return $this->hasMany(DokumentasiKegiatan::class, 'kegiatan_id');
    }
}
