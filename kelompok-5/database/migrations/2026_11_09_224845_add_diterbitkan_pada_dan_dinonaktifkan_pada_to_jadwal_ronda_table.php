<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal_ronda', function (Blueprint $table) {
            $table->dateTime('diterbitkan_pada')
                ->nullable()
                ->after('status');

            $table->dateTime('dinonaktifkan_pada')
                ->nullable()
                ->after('diterbitkan_pada');
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_ronda', function (Blueprint $table) {
            $table->dropColumn([
                'diterbitkan_pada',
                'dinonaktifkan_pada',
            ]);
        });
    }
};
