<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('token_akses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengguna_id')
                ->constrained('pengguna')
                ->cascadeOnDelete();

            $table->string('token_hash', 64)->unique();
            $table->dateTime('berlaku_sampai')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('token_akses');
    }
};
