<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TokenAkses extends Model
{
    protected $table = 'token_akses';

    protected $fillable = [
        'pengguna_id',
        'token_hash',
        'berlaku_sampai',
    ];

    protected $casts = [
        'berlaku_sampai' => 'datetime',
    ];

    // ─────────────────────────────────────────────
    // Relasi
    // ─────────────────────────────────────────────

    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_id');
    }

    // ─────────────────────────────────────────────
    // Helper Expiry
    // ─────────────────────────────────────────────

    public function sudahKedaluwarsa(): bool
    {
        // berlaku_sampai = null berarti token tidak expired (permanen)
        return $this->berlaku_sampai !== null
            && $this->berlaku_sampai->isPast();
    }

    public function masihBerlaku(): bool
    {
        return ! $this->sudahKedaluwarsa();
    }
}