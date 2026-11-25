<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KegiatanPanitia extends Model
{
    protected $table = 'kegiatan_panitia';

    protected $fillable = [
        'kegiatan_id',
        'nama_panitia',
        'jabatan',
        'tugas',
    ];

    /**
     * Kegiatan yang memiliki panitia.
     */
    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class, 'kegiatan_id');
    }

}
