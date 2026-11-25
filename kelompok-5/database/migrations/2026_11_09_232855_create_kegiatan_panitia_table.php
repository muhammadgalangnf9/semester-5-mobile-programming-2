<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kegiatan_panitia', function (Blueprint $table) {
            $table->id();

            // Relasi ke kegiatan
            $table->foreignId('kegiatan_id')
                ->constrained('kegiatan')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Relasi ke pengguna (opsional)
            $table->foreignId('pengguna_id')
                ->nullable()
                ->constrained('pengguna')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            // Identitas panitia
            $table->string('nama_panitia', 150);
            $table->string('jabatan', 100);
            $table->text('tugas')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kegiatan_panitia');
    }
};
