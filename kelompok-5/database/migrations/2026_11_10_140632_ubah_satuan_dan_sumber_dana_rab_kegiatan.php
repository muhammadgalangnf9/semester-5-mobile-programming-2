<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rab_kegiatan', function (Blueprint $table) {
            $table->string('satuan', 100)->change();
        });

        Schema::table('rab_kegiatan', function (Blueprint $table) {
            $table->string('sumber_dana', 100)
                ->nullable(false)
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('rab_kegiatan', function (Blueprint $table) {
            $table->enum('satuan', [
                'buah', 'unit', 'pcs', 'set', 'paket', 'pasang',
                'lusin', 'kodi', 'rim', 'kotak', 'dus', 'botol',
                'kaleng', 'bungkus', 'sachet', 'lembar', 'batang',
                'gulungan', 'gram', 'kilogram', 'kuintal', 'ton',
                'mililiter', 'liter', 'galon', 'milimeter',
                'sentimeter', 'meter', 'kilometer', 'meter_persegi',
                'menit', 'jam', 'hari', 'minggu', 'bulan',
                'porsi', 'orang', 'perjalanan', 'kendaraan',
                'tiket', 'sesi', 'kegiatan'
            ])->change();

            $table->string('sumber_dana', 100)
                ->nullable()
                ->change();
        });
    }
};
