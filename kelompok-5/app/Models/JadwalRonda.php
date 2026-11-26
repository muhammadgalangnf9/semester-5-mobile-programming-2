<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalRonda extends Model
{
    protected $table = 'jadwal_ronda';

    protected $fillable = [
        'dibuat_oleh',
        'pengubah_id',
        'hari',
        'peserta',
        'waktu_mulai',
        'waktu_selesai',
        'gang',
        'keterangan',
        'status',
        'diterbitkan_pada',
        'dinonaktifkan_pada',
    ];

    protected $casts = [
        'hari' => 'array',
        'peserta' => 'array',
        'diterbitkan_pada' => 'datetime',
        'dinonaktifkan_pada' => 'datetime',
    ];

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'dibuat_oleh');
    }

    public function pengubah(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'pengubah_id');
    }
}
