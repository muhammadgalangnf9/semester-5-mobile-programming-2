<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengumuman', function (Blueprint $table) {
            $table->dropSoftDeletes();

            $table->enum('status', [
                'draft',
                'terbit',
                'nonaktif',
            ])->default('draft')->change();
        });
    }

    public function down(): void
    {
        Schema::table('pengumuman', function (Blueprint $table) {
            $table->enum('status', [
                'draft',
                'terbit',
            ])->default('draft')->change();

            $table->softDeletes();
        });
    }
};