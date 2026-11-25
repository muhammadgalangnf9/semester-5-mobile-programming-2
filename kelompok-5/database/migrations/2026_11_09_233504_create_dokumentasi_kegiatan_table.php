<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dokumentasi_kegiatan', function (Blueprint $table) {
            $table->id();

            // Relasi ke kegiatan
            $table->foreignId('kegiatan_id')
                ->constrained('kegiatan')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Informasi dokumentasi
            $table->string('nama_dokumentasi', 150);

            $table->enum('jenis', [
                'foto',
                'video',
            ]);

            // Link Google Drive
            $table->string('link_drive', 500);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumentasi_kegiatan');
    }
};
