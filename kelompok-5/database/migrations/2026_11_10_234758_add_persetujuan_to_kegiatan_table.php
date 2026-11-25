<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        // Tambahkan status baru sementara, tanpa menghapus status lama.
        DB::statement("
            ALTER TABLE kegiatan
            MODIFY status ENUM(
                'draft',
                'terbit',
                'selesai',
                'nonaktif',
                'menunggu_persetujuan',
                'disetujui',
                'ditolak'
            ) NOT NULL DEFAULT 'draft'
        ");

        // Konversi status lama.
        DB::table('kegiatan')
            ->where('status', 'terbit')
            ->update(['status' => 'disetujui']);

        // Terapkan daftar status final.
        DB::statement("
            ALTER TABLE kegiatan
            MODIFY status ENUM(
                'draft',
                'menunggu_persetujuan',
                'disetujui',
                'ditolak',
                'selesai',
                'nonaktif'
            ) NOT NULL DEFAULT 'draft'
        ");

        // Tambahkan kolom persetujuan.
        Schema::table('kegiatan', function (Blueprint $table) {
            $table->unsignedBigInteger('disetujui_oleh')
                ->nullable()
                ->after('diterbitkan_pada');

            $table->text('catatan_persetujuan')
                ->nullable()
                ->after('disetujui_oleh');
        });
    }

    public function down(): void
    {
        // Siapkan status lama agar konversi dapat dilakukan.
        DB::statement("
            ALTER TABLE kegiatan
            MODIFY status ENUM(
                'draft',
                'terbit',
                'selesai',
                'nonaktif',
                'menunggu_persetujuan',
                'disetujui',
                'ditolak'
            ) NOT NULL DEFAULT 'draft'
        ");

        // Kembalikan status disetujui menjadi terbit.
        DB::table('kegiatan')
            ->where('status', 'disetujui')
            ->update(['status' => 'terbit']);

        // Status baru lainnya dikembalikan menjadi draft.
        DB::table('kegiatan')
            ->whereIn('status', [
                'menunggu_persetujuan',
                'ditolak',
            ])
            ->update(['status' => 'draft']);

        Schema::table('kegiatan', function (Blueprint $table) {
            $table->dropColumn([
                'disetujui_oleh',
                'catatan_persetujuan',
            ]);
        });

        // Kembalikan enum seperti semula.
        DB::statement("
            ALTER TABLE kegiatan
            MODIFY status ENUM(
                'draft',
                'terbit',
                'selesai',
                'nonaktif'
            ) NOT NULL DEFAULT 'draft'
        ");
    }
};
