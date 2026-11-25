<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal_ronda', function (Blueprint $table) {
            $table->json('hari_baru')->nullable()->after('id');
        });

        // Memindahkan hari lama ke format JSON.
        DB::statement("
            UPDATE jadwal_ronda
            SET hari_baru = JSON_ARRAY(hari)
        ");

        Schema::table('jadwal_ronda', function (Blueprint $table) {
            $table->dropColumn('hari');
        });

        Schema::table('jadwal_ronda', function (Blueprint $table) {
            $table->renameColumn('hari_baru', 'hari');
            $table->renameColumn('lokasi', 'gang');
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_ronda', function (Blueprint $table) {
            $table->enum('hari_lama', [
                'Senin',
                'Selasa',
                'Rabu',
                'Kamis',
                'Jumat',
                'Sabtu',
                'Minggu',
            ])->nullable()->after('id');
        });

        DB::statement("
            UPDATE jadwal_ronda
            SET hari_lama = JSON_UNQUOTE(JSON_EXTRACT(hari, '$[0]'))
        ");

        Schema::table('jadwal_ronda', function (Blueprint $table) {
            $table->dropColumn('hari');
        });

        Schema::table('jadwal_ronda', function (Blueprint $table) {
            $table->renameColumn('hari_lama', 'hari');
            $table->renameColumn('gang', 'lokasi');
        });
    }
};
