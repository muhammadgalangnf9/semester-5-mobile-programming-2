<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_ronda', function (Blueprint $table) {
            $table->id();

            $table->date('tanggal');
            $table->time('waktu_mulai');
            $table->time('waktu_selesai');

            $table->string('lokasi', 150);
            $table->text('keterangan')->nullable();

            $table->enum('status', ['aktif', 'nonaktif'])
                ->default('aktif');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_ronda');
    }
};