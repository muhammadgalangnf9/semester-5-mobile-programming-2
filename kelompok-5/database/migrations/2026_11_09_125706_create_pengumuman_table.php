<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengumuman', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pengguna_id')
                ->constrained('pengguna')
                ->restrictOnDelete();

            $table->string('judul', 150);
            $table->longText('isi');

            $table->enum('status', ['draft', 'terbit'])
                ->default('draft');

            $table->timestamp('diterbitkan_pada')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengumuman');
    }
};