<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rab_kegiatan', function (Blueprint $table) {
            $table->id();

            // Relasi ke kegiatan
            $table->foreignId('kegiatan_id')
                ->constrained('kegiatan')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Rincian anggaran
            $table->string('nama_kebutuhan', 150);
            $table->string('kategori_pengeluaran', 100);
            $table->decimal('jumlah', 12, 2);
            
            // Satuan kebutuhan
            $table->enum('satuan', [
                'buah',
                'unit',
                'pcs',
                'set',
                'paket',
                'pasang',
                'lusin',
                'kodi',
                'rim',
                'kotak',
                'dus',
                'botol',
                'kaleng',
                'bungkus',
                'sachet',
                'lembar',
                'batang',
                'gulungan',
                'gram',
                'kilogram',
                'kuintal',
                'ton',
                'mililiter',
                'liter',
                'galon',
                'milimeter',
                'sentimeter',
                'meter',
                'kilometer',
                'meter_persegi',
                'menit',
                'jam',
                'hari',
                'minggu',
                'bulan',
                'porsi',
                'orang',
                'perjalanan',
                'kendaraan',
                'tiket',
                'sesi',
                'kegiatan',
            ]);

            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('total_biaya', 15, 2);

            // Asal dana
            $table->string('sumber_dana', 100)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rab_kegiatan');
    }
};
