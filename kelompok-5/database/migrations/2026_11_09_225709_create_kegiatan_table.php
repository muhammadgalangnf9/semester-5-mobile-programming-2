<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kegiatan', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pengguna_id')
                ->constrained('pengguna')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('nama_kegiatan', 150);
            $table->text('deskripsi');
            $table->string('jenis_kegiatan', 100);

            $table->dateTime('tanggal_mulai');
            $table->dateTime('tanggal_selesai');

            $table->string('lokasi', 150);
            $table->string('link_poster', 500)->nullable();

            $table->enum('status', [
                'draft',
                'terbit',
                'selesai',
                'nonaktif',
            ])->default('draft');

            $table->dateTime('diterbitkan_pada')->nullable();
            $table->dateTime('dinonaktifkan_pada')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kegiatan');
    }
};
