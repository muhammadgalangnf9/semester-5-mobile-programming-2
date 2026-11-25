<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengumuman', function (Blueprint $table) {
            $table->enum('status', [
                'draft',
                'menunggu_persetujuan',
                'terbit',
                'ditolak',
                'nonaktif',
            ])->default('draft')->change();

            $table->foreignId('disetujui_oleh')
                ->nullable()
                ->after('diterbitkan_pada')
                ->constrained('pengguna')
                ->nullOnDelete();
            $table->text('catatan_persetujuan')->nullable();
        });
    }

    public function down(): void
    {
        DB::table('pengumuman')
            ->whereIn('status', ['menunggu_persetujuan', 'ditolak'])
            ->update(['status' => 'draft']);

        Schema::table('pengumuman', function (Blueprint $table) {
            $table->dropConstrainedForeignId('disetujui_oleh');
            $table->dropColumn('catatan_persetujuan');

            $table->enum('status', [
                'draft',
                'terbit',
                'nonaktif',
            ])->default('draft')->change();
        });
    }
};
