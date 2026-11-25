<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiKegiatan extends Model
{
    protected $table = 'transaksi_kegiatan';

    protected $fillable = [
        'kegiatan_id',
        'jenis_transaksi',
        'kategori',
        'tanggal_transaksi',
        'nominal',
        'keterangan',
        'link_bukti',
    ];

    protected $casts = [
        'tanggal_transaksi' => 'date',
        'nominal' => 'decimal:2',
    ];

    /**
     * Kegiatan yang memiliki transaksi.
     */
    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class, 'kegiatan_id');
    }
}
