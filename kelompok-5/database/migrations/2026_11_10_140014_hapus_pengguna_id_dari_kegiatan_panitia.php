<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kegiatan_panitia', function (Blueprint $table) {
            $table->dropForeign(['pengguna_id']);
            $table->dropColumn('pengguna_id');
        });
    }

    public function down(): void
    {
        Schema::table('kegiatan_panitia', function (Blueprint $table) {
            $table->foreignId('pengguna_id')
                ->nullable()
                ->after('kegiatan_id')
                ->constrained('pengguna')
                ->nullOnDelete();
        });
    }
};
