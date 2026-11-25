<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengumuman extends Model
{
    protected $table = 'pengumuman';

    protected $fillable = [
        'pengguna_id',
        'judul',
        'isi',
        'status',
        'diterbitkan_pada',
        'dinonaktifkan_pada',
        'disetujui_oleh',
        'catatan_persetujuan',
    ];

    protected $casts = [
        'diterbitkan_pada' => 'datetime',
        'dinonaktifkan_pada' => 'datetime',
    ];

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_id');
    }

    public function disetujuiOleh(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'disetujui_oleh');
    }
}