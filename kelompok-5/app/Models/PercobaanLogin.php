<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PercobaanLogin extends Model
{
    protected $table = 'percobaan_login';

    protected $fillable = [
        'username',
        'alamat_ip',
        'jumlah_gagal',
        'dikunci_sampai',
    ];

    protected $casts = [
        'dikunci_sampai' => 'datetime',
        'jumlah_gagal'   => 'integer',
    ];

    // ─────────────────────────────────────────────
    // Helper Lockout
    // ─────────────────────────────────────────────

    public function sedangDikunci(): bool
    {
        return $this->dikunci_sampai !== null
            && $this->dikunci_sampai->isFuture();
    }

    public function sisaDetikKunci(): int
    {
        if (! $this->sedangDikunci()) {
            return 0;
        }
        return now()->diffInSeconds($this->dikunci_sampai, false);
    }
}