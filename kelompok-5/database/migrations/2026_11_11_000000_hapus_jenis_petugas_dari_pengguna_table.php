<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengguna', function (Blueprint $table) {
            $table->dropColumn('jenis_petugas');
        });
    }

    public function down(): void
    {
        Schema::table('pengguna', function (Blueprint $table) {
            $table->enum('jenis_petugas', [
                'pj_bank_sampah',
                'pj_iuran_denda',
                'pj_kegiatan_informasi',
            ])->nullable()->after('password');
        });
    }
};
