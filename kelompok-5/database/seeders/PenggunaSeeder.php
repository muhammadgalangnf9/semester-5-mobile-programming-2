<?php

namespace Database\Seeders;

use App\Models\Pengguna;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class PenggunaSeeder extends Seeder
{
    public function run(): void
    {
        // Mengambil ID role dari tabel role
        $getRoleId = function (string $namaRole): int {
            $id = DB::table('role')
                ->where('nama_role', $namaRole)
                ->value('id');

            if (! $id) {
                throw new RuntimeException(
                    "Role {$namaRole} belum tersedia. Jalankan RoleSeeder terlebih dahulu."
                );
            }

            return $id;
        };

        // 1. Ketua RT
        Pengguna::updateOrCreate(
            ['username' => 'ketuart01'],
            [
                'nama' => 'Ketua RT',
                'role_id' => $getRoleId('ketua_rt'),
                'gang' => 'Gang E4/E5',
                'blok' => 'E4/12',
                'password' => Hash::make('rahasia123'),
                'status' => 'aktif',
            ]
        );

        // 2. Warga PJ (Budi)
        Pengguna::updateOrCreate(
            ['username' => 'budi'],
            [
                'nama' => 'Budi',
                'role_id' => $getRoleId('warga_pj_kegiatan_dan_jadwal_ronda'),
                'gang' => 'Gang E6',
                'blok' => 'E6/02',
                'password' => Hash::make('rahasia123'),
                'status' => 'aktif',
            ]
        );

        // 3. Warga PJ (Siti)
        Pengguna::updateOrCreate(
            ['username' => 'siti'],
            [
                'nama' => 'Siti',
                'role_id' => $getRoleId('warga_pj_kegiatan_dan_jadwal_ronda'),
                'gang' => 'Gang E4/E5',
                'blok' => 'E4/01',
                'password' => Hash::make('rahasia123'),
                'status' => 'aktif',
            ]
        );

        // 4. Warga PJ (Andi)
        Pengguna::updateOrCreate(
            ['username' => 'andi'],
            [
                'nama' => 'Andi',
                'role_id' => $getRoleId('warga_pj_kegiatan_dan_jadwal_ronda'),
                'gang' => 'Gang E2/E3',
                'blok' => 'E2/01',
                'password' => Hash::make('rahasia123'),
                'status' => 'aktif',
            ]
        );

        // 5. Warga Biasa
        Pengguna::updateOrCreate(
            ['username' => 'warga01'],
            [
                'nama' => 'Warga 01',
                'role_id' => $getRoleId('warga_biasa'),
                'gang' => 'Gang C16/E1',
                'blok' => 'C16/01',
                'password' => Hash::make('rahasia123'),
                'status' => 'aktif',
            ]
        );

        $this->command->info(
            'PenggunaSeeder selesai. Akun pengguna berhasil dibuat.'
        );
    }
}
