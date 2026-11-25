<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailJadwalRonda extends Model
{
    protected $table = 'detail_jadwal_ronda';

    protected $fillable = [
        'jadwal_ronda_id',
        'pengguna_id',
        'keterangan',
    ];

    public function jadwalRonda(): BelongsTo
    {
        return $this->belongsTo(
            JadwalRonda::class,
            'jadwal_ronda_id'
        );
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(
            Pengguna::class,
            'pengguna_id'
        );
    }
}