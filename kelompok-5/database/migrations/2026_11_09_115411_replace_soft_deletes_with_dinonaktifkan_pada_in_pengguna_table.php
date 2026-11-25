<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengguna', function (Blueprint $table) {
            $table->dropSoftDeletes();

            $table->timestamp('dinonaktifkan_pada')
                ->nullable()
                ->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('pengguna', function (Blueprint $table) {
            $table->dropColumn('dinonaktifkan_pada');

            $table->softDeletes();
        });
    }
};
