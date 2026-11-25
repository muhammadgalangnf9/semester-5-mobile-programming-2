<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            $rolePJ = DB::table('role')
                ->where('nama_role', 'warga_pj_kegiatan_dan_jadwal_ronda')
                ->value('id');
            $roleWargaLama = DB::table('role')
                ->where('nama_role', 'warga')
                ->value('id');

            if ($roleWargaLama && $rolePJ) {
                DB::table('pengguna')
                    ->where('role_id', $roleWargaLama)
                    ->update(['role_id' => $rolePJ]);
                DB::table('role')->where('id', $roleWargaLama)->delete();
            } elseif ($roleWargaLama) {
                DB::table('role')
                    ->where('id', $roleWargaLama)
                    ->update([
                        'nama_role' => 'warga_pj_kegiatan_dan_jadwal_ronda',
                    ]);
            }

            $roleTimIT = DB::table('role')
                ->where('nama_role', 'tim_it')
                ->value('id');

            if ($roleTimIT) {
                $roleWargaBiasa = DB::table('role')
                    ->where('nama_role', 'warga_biasa')
                    ->value('id');

                if (! $roleWargaBiasa) {
                    throw new \RuntimeException(
                        'Role warga_biasa diperlukan untuk menghapus role tim_it.'
                    );
                }

                DB::table('pengguna')
                    ->where('role_id', $roleTimIT)
                    ->update(['role_id' => $roleWargaBiasa]);
                DB::table('role')->where('id', $roleTimIT)->delete();
            }
        });
    }

    public function down(): void
    {
        DB::transaction(function (): void {
            DB::table('role')
                ->where('nama_role', 'warga_pj_kegiatan_dan_jadwal_ronda')
                ->update(['nama_role' => 'warga']);

            if (! DB::table('role')->where('nama_role', 'tim_it')->exists()) {
                DB::table('role')->insert([
                    'nama_role' => 'tim_it',
                    'deskripsi' => 'Pengelola teknis sistem',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }
};
