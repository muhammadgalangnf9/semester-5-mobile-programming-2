<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Pengguna extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'pengguna';

    protected $fillable = [
        'role_id',
        'nama',
        'username',
        'gang',
        'blok',
        'jenis_kelamin',
        'no_hp',
        'email',
        'password',
        'status',
        'dinonaktifkan_pada',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'dinonaktifkan_pada' => 'datetime',
    ];

    public function isKetuaRt(): bool
    {
        return (int) $this->role_id === 1;
    }

    public static function blokSesuaiGang(mixed $gang, mixed $blok): bool
    {
        if (! is_string($gang) || ! is_string($blok) || trim($blok) === '') {
            return false;
        }

        $daftarBlok = [
            'Gang C16/E1' => ['C16', 'E1'],
            'Gang E2/E3' => ['E2', 'E3'],
            'Gang E4/E5' => ['E4', 'E5'],
            'Gang E6' => ['E6'],
        ];

        if (
            ! isset($daftarBlok[$gang]) ||
            ! preg_match('/^([^\/]+)\/(\d+)$/', $blok, $bagian)
        ) {
            return false;
        }

        return in_array($bagian[1], $daftarBlok[$gang], true);
    }

    // =========================================
    // RELASI
    // =========================================

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function tokenAkses(): HasMany
    {
        return $this->hasMany(TokenAkses::class, 'pengguna_id');
    }

    // =========================================
    // HELPER ROLE
    // =========================================

    /**
     * Memeriksa role berdasarkan ID.
     */
    public function memilikiRole(int $roleId): bool
    {
        return (int) $this->role_id === $roleId;
    }

    /**
     * Mengambil nama role dari tabel role.
     */
    public function labelRole(): string
    {
        return $this->role?->nama_role ?? '-';
    }

    public function daftarAkses(): array
    {
        $akses = [
            'dashboard' => [],
            'kelola_warga' => [],
            'pengumuman' => [],
            'jadwal_ronda' => [],
            'kegiatan' => [],
        ];

        // Ketua RT
        if ($this->isKetuaRt()) {
            $akses['dashboard'] = ['read'];

            $akses['kelola_warga'] = [
                'create',
                'read',
                'update',
                'delete',
            ];

            $akses['pengumuman'] = ['read'];

            $akses['jadwal_ronda'] = ['read'];

            $akses['kegiatan'] = [
                'read',
                'approve',
            ];
        }

        // Warga dengan akses pengelolaan
        elseif ((int) $this->role_id === 2) {
            $akses['dashboard'] = ['read'];

            $akses['pengumuman'] = [
                'create',
                'read',
                'update',
                'delete',
            ];

            $akses['jadwal_ronda'] = [
                'create',
                'read',
                'update',
                'delete',
            ];

            $akses['kegiatan'] = [
                'create',
                'read',
                'update',
                'delete',
            ];
        }

        // Warga Biasa
        elseif ((int) $this->role_id === 3) {
            $akses['dashboard'] = ['read'];
            $akses['pengumuman'] = ['read'];
            $akses['jadwal_ronda'] = ['read'];
            $akses['kegiatan'] = ['read'];
        }

        return $akses;
    }

    // =========================================
    // HELPER STATUS AKUN
    // =========================================

    /**
     * Memeriksa apakah akun sedang aktif.
     */
    public function akunAktif(): bool
    {
        return $this->status === 'aktif';
    }

    public function detailJadwalRonda(): HasMany
    {
        return $this->hasMany(
            DetailJadwalRonda::class,
            'pengguna_id'
        );
    }

    public function jadwalRonda(): BelongsToMany
    {
        return $this->belongsToMany(
            JadwalRonda::class,
            'detail_jadwal_ronda',
            'pengguna_id',
            'jadwal_ronda_id'
        )->withPivot('keterangan')
            ->withTimestamps();
    }
}
