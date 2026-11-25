
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengguna', function (Blueprint $table) {
            $table->id();

            $table->foreignId('role_id')
                ->constrained('role')
                ->restrictOnDelete();

            $table->string('nama', 100);
            $table->string('username', 50)->unique();

            $table->enum('gang', [
                'Gang E6',
                'Gang E4/E5',
                'Gang E2/E3',
                'Gang C16/E1',
            ])->nullable();

            $table->enum('jenis_kelamin', [
                'laki_laki',
                'perempuan',
            ])->nullable();

            $table->string('no_hp', 20)->nullable();
            $table->string('email', 100)->nullable()->unique();
            $table->string('password', 255);

            $table->enum('jenis_petugas', [
                'pj_bank_sampah',
                'pj_iuran_denda',
                'pj_kegiatan_informasi',
            ])->nullable();

            $table->enum('status', [
                'aktif',
                'nonaktif',
            ])->default('aktif');

            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengguna');
    }
};
