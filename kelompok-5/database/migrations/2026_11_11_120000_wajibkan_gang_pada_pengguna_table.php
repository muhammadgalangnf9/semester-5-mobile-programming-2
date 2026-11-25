<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $adaGangKosong = DB::table('pengguna')
            ->whereNull('gang')
            ->orWhere('gang', '')
            ->exists();

        if ($adaGangKosong) {
            throw new \RuntimeException(
                'Kolom gang masih kosong. Lengkapi gang semua pengguna sebelum menjalankan migrasi.'
            );
        }

        Schema::table('pengguna', function (Blueprint $table) {
            $table->enum('gang', [
                'Gang E6',
                'Gang E4/E5',
                'Gang E2/E3',
                'Gang C16/E1',
            ])->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('pengguna', function (Blueprint $table) {
            $table->enum('gang', [
                'Gang E6',
                'Gang E4/E5',
                'Gang E2/E3',
                'Gang C16/E1',
            ])->nullable()->change();
        });
    }
};
