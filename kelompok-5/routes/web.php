<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\PengumumanController;
use App\Http\Controllers\Web\JadwalRondaController;
use App\Http\Controllers\Web\KegiatanController;
use App\Http\Controllers\Web\KegiatanPanitiaController;
use App\Http\Controllers\Web\RabKegiatanController;
use App\Http\Controllers\Web\TransaksiKegiatanController;
use App\Http\Controllers\Web\PesertaKegiatanController;
use App\Http\Controllers\Web\DokumentasiKegiatanController;

use App\Http\Controllers\Web\KetuaRT\KelolaWargaController;
use App\Http\Controllers\Web\KetuaRT\KelolaRoleController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Halaman Login
Route::middleware('guest')->group(function () {

    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.proses');

});

// Logout
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Halaman setelah login
Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/dashboard/pengumuman', [DashboardController::class, 'pengumuman'])
        ->name('dashboard.pengumuman');


    /*
    |--------------------------------------------------------------------------
    | Pengumuman (Semua Role yang Memiliki Akses)
    |--------------------------------------------------------------------------
    */

    Route::prefix('pengumuman')
        ->name('pengumuman.')
        ->controller(PengumumanController::class)
        ->group(function () {

            Route::get('/', 'index')
                ->name('index');

            Route::get('/create', 'create')
                ->name('create');

            Route::post('/', 'store')
                ->name('store');

            Route::get('/{pengumuman}/edit', 'edit')
                ->name('edit');

            Route::get('/{pengumuman}', 'show')
                ->name('show');

            Route::put('/{pengumuman}', 'update')
                ->name('update');

            Route::post('/{pengumuman}/ajukan', 'ajukan')
                ->name('ajukan');

            Route::post('/{pengumuman}/setujui', 'setujui')
                ->name('setujui');

            Route::post('/{pengumuman}/tolak', 'tolak')
                ->name('tolak');

            Route::patch('/{pengumuman}/ubah-status', 'ubahStatus')
                ->name('ubah-status');

        });


    /*
    |--------------------------------------------------------------------------
    | Jadwal Ronda (Semua Role yang Memiliki Akses)
    |--------------------------------------------------------------------------
    */

    Route::prefix('jadwal-ronda')
        ->name('jadwal_ronda.')
        ->controller(JadwalRondaController::class)
        ->group(function () {

            Route::get('/', 'index')
                ->name('index');

            Route::get('/create', 'create')
                ->name('create');

            Route::post('/', 'store')
                ->name('store');

            Route::get('/{jadwalRonda}/edit', 'edit')
                ->name('edit');

            Route::get('/{jadwalRonda}', 'show')
                ->name('show');

            Route::put('/{jadwalRonda}', 'update')
                ->name('update');

            Route::patch('/{jadwalRonda}/ubah-status', 'ubahStatus')
                ->name('ubah_status');

        });


    /*
    |--------------------------------------------------------------------------
    | Fitur Khusus Ketua RT (ID Role: 1)
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:1')
        ->prefix('ketua-rt')
        ->name('ketua-rt.')
        ->group(function () {

            /*
            |--------------------------------------------------------------------------
            | Kelola Warga
            |--------------------------------------------------------------------------
            */

            Route::resource('kelola-warga', KelolaWargaController::class)
                ->except(['destroy'])
                ->parameters([
                    'kelola-warga' => 'pengguna',
                ])
                ->names('kelola-warga');

            Route::patch(
                'kelola-warga/{pengguna}/ubah-status',
                [KelolaWargaController::class, 'ubahStatus']
            )->name('kelola-warga.ubah-status');


            /*
            |--------------------------------------------------------------------------
            | Kelola Role
            |--------------------------------------------------------------------------
            */

            Route::prefix('kelola-role')
                ->name('kelola-role.')
                ->controller(KelolaRoleController::class)
                ->group(function () {

                    Route::get('/', 'index')
                        ->name('index');

                    Route::get('/{role}/edit', 'edit')
                        ->name('edit');

                    Route::get('/{role}', 'show')
                        ->name('show');

                    Route::put('/{role}', 'update')
                        ->name('update');

                });

        });


    /*
    |--------------------------------------------------------------------------
    | Kegiatan (Semua Role yang Memiliki Akses)
    |--------------------------------------------------------------------------
    */

    // Route utama dan route pendukung Kegiatan
    Route::prefix('kegiatan')
        ->name('kegiatan.')
        ->group(function () {

            /*
            |--------------------------------------------------------------------------
            | Panitia Kegiatan
            |--------------------------------------------------------------------------
            */

            Route::prefix('{kegiatan}/panitia')
                ->name('panitia.')
                ->controller(KegiatanPanitiaController::class)
                ->group(function () {

                    Route::post('/', 'store')
                        ->name('store');

                    Route::put('/{panitia}', 'update')
                        ->name('update');

                    Route::delete('/{panitia}', 'destroy')
                        ->name('destroy');

                });


            /*
            |--------------------------------------------------------------------------
            | Rencana Anggaran Biaya (RAB)
            |--------------------------------------------------------------------------
            */

            Route::prefix('{kegiatan}/rab')
                ->name('rab.')
                ->controller(RabKegiatanController::class)
                ->group(function () {

                    Route::post('/', 'store')
                        ->name('store');

                    Route::put('/{rab}', 'update')
                        ->name('update');

                    Route::delete('/{rab}', 'destroy')
                        ->name('destroy');

                });


            /*
            |--------------------------------------------------------------------------
            | Transaksi Kegiatan
            |--------------------------------------------------------------------------
            */

            Route::prefix('{kegiatan}/transaksi')
                ->name('transaksi.')
                ->controller(TransaksiKegiatanController::class)
                ->group(function () {

                    Route::post('/', 'store')
                        ->name('store');

                    Route::put('/{transaksi}', 'update')
                        ->name('update');

                    Route::delete('/{transaksi}', 'destroy')
                        ->name('destroy');

                });


            /*
            |--------------------------------------------------------------------------
            | Peserta Kegiatan
            |--------------------------------------------------------------------------
            */

            Route::prefix('{kegiatan}/peserta')
                ->name('peserta.')
                ->controller(PesertaKegiatanController::class)
                ->group(function () {

                    Route::post('/', 'store')
                        ->name('store');

                    Route::put('/{peserta}', 'update')
                        ->name('update');

                    Route::delete('/{peserta}', 'destroy')
                        ->name('destroy');

                    Route::patch(
                        '/{peserta}/ubah-kehadiran',
                        'ubahKehadiran'
                    )->name('ubah_kehadiran');

                });


            /*
            |--------------------------------------------------------------------------
            | Dokumentasi Kegiatan
            |--------------------------------------------------------------------------
            */

            Route::prefix('{kegiatan}/dokumentasi')
                ->name('dokumentasi.')
                ->controller(DokumentasiKegiatanController::class)
                ->group(function () {

                    Route::post('/', 'store')
                        ->name('store');

                    Route::put('/{dokumentasi}', 'update')
                        ->name('update');

                    Route::delete('/{dokumentasi}', 'destroy')
                        ->name('destroy');

                });


            /*
            |--------------------------------------------------------------------------
            | Kegiatan Utama
            |--------------------------------------------------------------------------
            */

            Route::controller(KegiatanController::class)
                ->group(function () {

                    Route::get('/', 'index')
                        ->name('index');

                    Route::get('/create', 'create')
                        ->name('create');

                    Route::post('/', 'store')
                        ->name('store');

                    Route::get('/{kegiatan}/edit', 'edit')
                        ->name('edit');

                    Route::get('/{kegiatan}', 'show')
                        ->name('show');

                    Route::put('/{kegiatan}', 'update')
                        ->name('update');

                    Route::patch(
                        '/{kegiatan}/ubah-status',
                        'ubahStatus'
                    )->name('ubah_status');

                });

        });

});
