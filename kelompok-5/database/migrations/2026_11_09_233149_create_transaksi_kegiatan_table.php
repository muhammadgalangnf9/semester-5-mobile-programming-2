<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_kegiatan', function (Blueprint $table) {
            $table->id();

            // Relasi ke kegiatan
            $table->foreignId('kegiatan_id')
                ->constrained('kegiatan')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Jenis transaksi
            $table->enum('jenis_transaksi', [
                'pemasukan',
                'pengeluaran',
            ]);

            // Informasi transaksi
            $table->string('kategori', 100);
            $table->date('tanggal_transaksi');
            $table->decimal('nominal', 15, 2);
            $table->text('keterangan')->nullable();

            // Bukti transaksi Google Drive
            $table->string('link_bukti', 500)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_kegiatan');
    }
};
