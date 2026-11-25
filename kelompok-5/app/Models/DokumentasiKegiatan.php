<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DokumentasiKegiatan extends Model
{
    protected $table = 'dokumentasi_kegiatan';

    protected $fillable = [
        'kegiatan_id',
        'nama_dokumentasi',
        'jenis',
        'link_drive',
    ];

    /**
     * Kegiatan yang memiliki dokumentasi.
     */
    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class, 'kegiatan_id');
    }
}
