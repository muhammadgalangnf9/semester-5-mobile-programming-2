<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peserta_kegiatan', function (Blueprint $table) {
            $table->id();

            // Relasi ke kegiatan
            $table->foreignId('kegiatan_id')
                ->constrained('kegiatan')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Identitas peserta
            $table->string('nama', 150);
            $table->string('gang', 100);

            // Status kehadiran
            $table->enum('status_kehadiran', [
                'belum_dikonfirmasi',
                'hadir',
                'tidak_hadir',
                'izin',
                'sakit',
            ])->default('belum_dikonfirmasi');

            // Keterangan tambahan
            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peserta_kegiatan');
    }
};
