<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_jadwal_ronda', function (Blueprint $table) {
            $table->id();

            $table->foreignId('jadwal_ronda_id')
                ->constrained('jadwal_ronda')
                ->cascadeOnDelete();

            $table->foreignId('pengguna_id')
                ->constrained('pengguna')
                ->restrictOnDelete();

            $table->string('keterangan', 255)->nullable();

            $table->timestamps();

            $table->unique(
                ['jadwal_ronda_id', 'pengguna_id'],
                'jadwal_pengguna_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_jadwal_ronda');
    }
};