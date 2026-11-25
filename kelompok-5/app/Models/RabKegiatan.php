<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RabKegiatan extends Model
{
    protected $table = 'rab_kegiatan';

    protected $fillable = [
        'kegiatan_id',
        'nama_kebutuhan',
        'kategori_pengeluaran',
        'jumlah',
        'satuan',
        'harga_satuan',
        'total_biaya',
        'sumber_dana',
    ];

    protected $casts = [
        'jumlah' => 'decimal:2',
        'harga_satuan' => 'decimal:2',
        'total_biaya' => 'decimal:2',
    ];

    /**
     * Kegiatan yang memiliki rincian anggaran.
     */
    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class, 'kegiatan_id');
    }
}
