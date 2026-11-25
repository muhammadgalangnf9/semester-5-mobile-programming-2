<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('role')->insert([
            [
                'nama_role' => 'ketua_rt',
                'deskripsi' => 'Pengelola utama sistem RT',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_role' => 'warga_pj_kegiatan_dan_jadwal_ronda',
                'deskripsi' => 'Warga PJ kegiatan dan jadwal ronda',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_role' => 'warga_biasa',
                'deskripsi' => 'Warga dengan akses umum',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
