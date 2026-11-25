<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('percobaan_login', function (Blueprint $table) {
            $table->id();
            $table->string('username', 50);
            $table->string('alamat_ip', 45);
            $table->unsignedTinyInteger('jumlah_gagal')->default(0);
            $table->dateTime('dikunci_sampai')->nullable();
            $table->timestamps();

            $table->unique(
                ['username', 'alamat_ip'],
                'percobaan_login_username_ip_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('percobaan_login');
    }
};
