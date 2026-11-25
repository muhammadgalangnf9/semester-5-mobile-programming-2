<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PesertaKegiatan extends Model
{
    protected $table = 'peserta_kegiatan';

    protected $fillable = [
        'kegiatan_id',
        'nama',
        'gang',
        'status_kehadiran',
        'keterangan',
    ];

    /**
     * Kegiatan yang diikuti peserta.
     */
    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class, 'kegiatan_id');
    }
}
