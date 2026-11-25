<?php

namespace Tests\Unit;

use App\Models\Pengguna;
use PHPUnit\Framework\TestCase;

class PenggunaAksesTest extends TestCase
{
    public function test_ketua_rt_can_read_and_approve_without_crud_access(): void
    {
        $pengguna = new Pengguna(['role_id' => 1]);

        $this->assertSame([
            'dashboard' => ['read'],
            'kelola_warga' => ['create', 'read', 'update', 'delete'],
            'pengumuman' => ['read'],
            'jadwal_ronda' => ['read'],
            'kegiatan' => ['read', 'approve'],
        ], $pengguna->daftarAkses());
    }

    public function test_warga_pj_can_crud_menus_assigned_to_them(): void
    {
        $pengguna = new Pengguna(['role_id' => 2]);

        $this->assertSame([
            'dashboard' => ['read'],
            'kelola_warga' => [],
            'pengumuman' => ['create', 'read', 'update', 'delete'],
            'jadwal_ronda' => ['create', 'read', 'update', 'delete'],
            'kegiatan' => ['create', 'read', 'update', 'delete'],
        ], $pengguna->daftarAkses());
    }

    public function test_warga_biasa_has_read_only_access(): void
    {
        $pengguna = new Pengguna(['role_id' => 3]);

        $this->assertSame([
            'dashboard' => ['read'],
            'kelola_warga' => [],
            'pengumuman' => ['read'],
            'jadwal_ronda' => ['read'],
            'kegiatan' => ['read'],
        ], $pengguna->daftarAkses());
    }
}
