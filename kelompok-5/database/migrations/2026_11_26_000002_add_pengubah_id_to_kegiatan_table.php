<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kegiatan', function (Blueprint $table) {
            $table->foreignId('pengubah_id')
                ->nullable()
                ->after('pengaju_id')
                ->constrained('pengguna')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('kegiatan', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pengubah_id');
        });
    }
};
