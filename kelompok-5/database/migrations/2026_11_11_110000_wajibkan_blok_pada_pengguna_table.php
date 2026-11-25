<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $adaBlokKosong = DB::table('pengguna')
            ->whereNull('blok')
            ->orWhere('blok', '')
            ->exists();

        if ($adaBlokKosong) {
            throw new RuntimeException(
                'Kolom blok masih kosong. Lengkapi blok semua pengguna sebelum menjalankan migrasi.'
            );
        }

        Schema::table('pengguna', function (Blueprint $table) {
            $table->string('blok', 20)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('pengguna', function (Blueprint $table) {
            $table->string('blok', 20)->nullable()->change();
        });
    }
};
