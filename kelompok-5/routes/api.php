<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

// Ketua RT
use App\Http\Controllers\Api\KetuaRT\DashboardApiController as KetuaRTDashboardApiController;
use App\Http\Controllers\Api\KetuaRT\KelolaWargaApiController as KetuaRTKelolaWargaApiController;
use App\Http\Controllers\Api\KetuaRT\PengumumanApiController as KetuaRTPengumumanApiController;
use App\Http\Controllers\Api\KetuaRT\JadwalRondaApiController as KetuaRTJadwalRondaApiController;
use App\Http\Controllers\Api\KetuaRT\KegiatanApiController as KetuaRTKegiatanApiController;

// Warga PJ Kegiatan dan Jadwal Ronda
use App\Http\Controllers\Api\WargaPJKegiatandanJadwalRonda\DashboardApiController as WargaPJDashboardApiController;
use App\Http\Controllers\Api\WargaPJKegiatandanJadwalRonda\PengumumanApiController as WargaPJPengumumanApiController;
use App\Http\Controllers\Api\WargaPJKegiatandanJadwalRonda\JadwalRondaApiController as WargaPJJadwalRondaApiController;
use App\Http\Controllers\Api\WargaPJKegiatandanJadwalRonda\KegiatanApiController as WargaPJKegiatanApiController;
use App\Http\Controllers\Api\WargaPJKegiatandanJadwalRonda\KomponenKegiatanApiController as WargaPJKomponenKegiatanApiController;

// Warga Biasa
use App\Http\Controllers\Api\WargaBiasa\DashboardApiController as WargaBiasaDashboardApiController;
use App\Http\Controllers\Api\WargaBiasa\PengumumanApiController as WargaBiasaPengumumanApiController;
use App\Http\Controllers\Api\WargaBiasa\JadwalRondaApiController as WargaBiasaJadwalRondaApiController;
use App\Http\Controllers\Api\WargaBiasa\KegiatanApiController as WargaBiasaKegiatanApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Autentikasi Publik
Route::post('/login', [AuthController::class, 'login'])
    ->name('api.login');

// Endpoint yang membutuhkan Token Autentikasi
Route::middleware('auth.token')->group(function () {

    // Profil & Logout
    Route::get('/profil', [AuthController::class, 'me'])
        ->name('api.profil');

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('api.logout');

    /*
    |--------------------------------------------------------------------------
    | 1. API Ketua RT
    | Hak Akses:
    | - Dashboard: R
    | - Kelola Warga: CRUD
    | - Pengumuman: R
    | - Jadwal Ronda: R
    | - Kegiatan: R + Acc
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:ketua_rt')
        ->prefix('ketua-rt')
        ->name('api.ketua-rt.')
        ->group(function () {

            // 1. Dashboard (R)
            Route::get('/dashboard', [KetuaRTDashboardApiController::class, 'index'])
                ->name('dashboard');

            // 2. Kelola Warga (CRUD)
            Route::get('/warga', [KetuaRTKelolaWargaApiController::class, 'index'])
                ->name('warga.index');
            Route::post('/warga', [KetuaRTKelolaWargaApiController::class, 'store'])
                ->name('warga.store');
            Route::get('/warga/{pengguna}', [KetuaRTKelolaWargaApiController::class, 'show'])
                ->name('warga.show');
            Route::put('/warga/{pengguna}', [KetuaRTKelolaWargaApiController::class, 'update'])
                ->name('warga.update');
            Route::patch('/warga/{pengguna}/status', [KetuaRTKelolaWargaApiController::class, 'ubahStatus'])
                ->name('warga.status');

            // 3. Pengumuman (R + Acc)
            Route::get('/pengumuman', [KetuaRTPengumumanApiController::class, 'index'])
                ->name('pengumuman.index');
            Route::get('/pengumuman/{id}', [KetuaRTPengumumanApiController::class, 'show'])
                ->name('pengumuman.show');
            Route::patch('/pengumuman/{id}/setujui', [KetuaRTPengumumanApiController::class, 'setujui'])
                ->name('pengumuman.setujui');
            Route::patch('/pengumuman/{id}/tolak', [KetuaRTPengumumanApiController::class, 'tolak'])
                ->name('pengumuman.tolak');

            // 4. Jadwal Ronda (R)
            Route::get('/jadwal-ronda', [KetuaRTJadwalRondaApiController::class, 'index'])
                ->name('jadwal-ronda.index');
            Route::get('/jadwal-ronda/{id}', [KetuaRTJadwalRondaApiController::class, 'show'])
                ->name('jadwal-ronda.show');

            // 5. Kegiatan (R + Acc)
            Route::get('/kegiatan', [KetuaRTKegiatanApiController::class, 'index'])
                ->name('kegiatan.index');
            Route::get('/kegiatan/{id}', [KetuaRTKegiatanApiController::class, 'show'])
                ->name('kegiatan.show');
            Route::put('/kegiatan/{id}/setujui', [KetuaRTKegiatanApiController::class, 'setujui'])
                ->name('kegiatan.setujui');
            Route::put('/kegiatan/{id}/tolak', [KetuaRTKegiatanApiController::class, 'tolak'])
                ->name('kegiatan.tolak');
            Route::put('/kegiatan/{id}/batalkan-persetujuan', [KetuaRTKegiatanApiController::class, 'batalkanPersetujuan'])
                ->name('kegiatan.batalkan-persetujuan');
        });

    /*
    |--------------------------------------------------------------------------
    | 2. API Warga PJ Kegiatan dan Jadwal Ronda
    | Hak Akses:
    | - Dashboard: R
    | - Kelola Warga: -
    | - Pengumuman: CRUD
    | - Jadwal Ronda: CRUD
    | - Kegiatan: CRUD
    |--------------------------------------------------------------------------
    */
    $ruteWargaPJ = function () {
        // 1. Dashboard (R)
        Route::get('/dashboard', [WargaPJDashboardApiController::class, 'index'])
            ->name('dashboard');

        // 3. Pengumuman (CRUD)
        Route::get('/pengumuman', [WargaPJPengumumanApiController::class, 'index'])
            ->name('pengumuman.index');
        Route::post('/pengumuman', [WargaPJPengumumanApiController::class, 'store'])
            ->name('pengumuman.store');
        Route::get('/pengumuman/{pengumuman}', [WargaPJPengumumanApiController::class, 'show'])
            ->name('pengumuman.show');
        Route::put('/pengumuman/{pengumuman}', [WargaPJPengumumanApiController::class, 'update'])
            ->name('pengumuman.update');
        Route::patch('/pengumuman/{pengumuman}/status', [WargaPJPengumumanApiController::class, 'ubahStatus'])
            ->name('pengumuman.status');
        Route::patch('/pengumuman/{pengumuman}/ajukan', [WargaPJPengumumanApiController::class, 'ajukanPersetujuan'])
            ->name('pengumuman.ajukan');
        Route::delete('/pengumuman/{pengumuman}', [WargaPJPengumumanApiController::class, 'destroy'])
            ->name('pengumuman.destroy');

        // 4. Jadwal Ronda (CRUD)
        Route::get('/jadwal-ronda', [WargaPJJadwalRondaApiController::class, 'index'])
            ->name('jadwal-ronda.index');
        Route::get('/jadwal-ronda/peserta', [WargaPJJadwalRondaApiController::class, 'daftarPeserta'])
            ->name('jadwal-ronda.peserta');
        Route::post('/jadwal-ronda', [WargaPJJadwalRondaApiController::class, 'store'])
            ->name('jadwal-ronda.store');
        Route::get('/jadwal-ronda/{jadwalRonda}', [WargaPJJadwalRondaApiController::class, 'show'])
            ->name('jadwal-ronda.show');
        Route::put('/jadwal-ronda/{jadwalRonda}', [WargaPJJadwalRondaApiController::class, 'update'])
            ->name('jadwal-ronda.update');
        Route::patch('/jadwal-ronda/{jadwalRonda}/status', [WargaPJJadwalRondaApiController::class, 'ubahStatus'])
            ->name('jadwal-ronda.status');
        Route::delete('/jadwal-ronda/{jadwalRonda}', [WargaPJJadwalRondaApiController::class, 'destroy'])
            ->name('jadwal-ronda.destroy');

        // 5. Kegiatan (CRUD)
        Route::get('/kegiatan', [WargaPJKegiatanApiController::class, 'index'])
            ->name('kegiatan.index');
        Route::post('/kegiatan', [WargaPJKegiatanApiController::class, 'store'])
            ->name('kegiatan.store');
        Route::get('/kegiatan/{id}', [WargaPJKegiatanApiController::class, 'show'])
            ->name('kegiatan.show');
        Route::put('/kegiatan/{id}', [WargaPJKegiatanApiController::class, 'update'])
            ->name('kegiatan.update');
        Route::put('/kegiatan/{id}/ajukan', [WargaPJKegiatanApiController::class, 'ajukan'])
            ->name('kegiatan.ajukan');
        Route::put('/kegiatan/{id}/aktifkan', [WargaPJKegiatanApiController::class, 'aktifkan'])
            ->name('kegiatan.aktifkan');
        Route::delete('/kegiatan/{id}', [WargaPJKegiatanApiController::class, 'destroy'])
            ->name('kegiatan.destroy');

        // Komponen Kegiatan
        Route::controller(WargaPJKomponenKegiatanApiController::class)
            ->prefix('kegiatan/{kegiatan}')
            ->name('kegiatan.')
            ->group(function () {
                Route::post('/panitia', 'storePanitia')->name('panitia.store');
                Route::put('/panitia/{panitia}', 'updatePanitia')->name('panitia.update');
                Route::delete('/panitia/{panitia}', 'destroyPanitia')->name('panitia.destroy');

                Route::post('/rab', 'storeRab')->name('rab.store');
                Route::put('/rab/{rab}', 'updateRab')->name('rab.update');
                Route::delete('/rab/{rab}', 'destroyRab')->name('rab.destroy');

                Route::post('/transaksi', 'storeTransaksi')->name('transaksi.store');
                Route::put('/transaksi/{transaksi}', 'updateTransaksi')->name('transaksi.update');
                Route::delete('/transaksi/{transaksi}', 'destroyTransaksi')->name('transaksi.destroy');

                Route::post('/peserta', 'storePeserta')->name('peserta.store');
                Route::put('/peserta/{peserta}', 'updatePeserta')->name('peserta.update');
                Route::delete('/peserta/{peserta}', 'destroyPeserta')->name('peserta.destroy');

                Route::post('/dokumentasi', 'storeDokumentasi')->name('dokumentasi.store');
                Route::put('/dokumentasi/{dokumentasi}', 'updateDokumentasi')->name('dokumentasi.update');
                Route::delete('/dokumentasi/{dokumentasi}', 'destroyDokumentasi')->name('dokumentasi.destroy');
            });
    };

    Route::middleware('role:warga_pj_kegiatan_dan_jadwal_ronda')
        ->prefix('warga-pj')
        ->name('api.warga-pj.')
        ->group($ruteWargaPJ);

    Route::middleware('role:warga_pj_kegiatan_dan_jadwal_ronda')
        ->prefix('warga-pj-kegiatan-dan-jadwal-ronda')
        ->name('api.warga-pj-kegiatan-dan-jadwal-ronda.')
        ->group($ruteWargaPJ);

    /*
    |--------------------------------------------------------------------------
    | 3. API Warga Biasa
    | Hak Akses:
    | - Dashboard: R
    | - Kelola Warga: -
    | - Pengumuman: R
    | - Jadwal Ronda: R
    | - Kegiatan: R
    |--------------------------------------------------------------------------
    */
    $ruteWargaBiasa = function () {
        // 1. Dashboard (R)
        Route::get('/dashboard', [WargaBiasaDashboardApiController::class, 'index'])
            ->name('dashboard');

        // 3. Pengumuman (R)
        Route::get('/pengumuman', [WargaBiasaPengumumanApiController::class, 'index'])
            ->name('pengumuman.index');
        Route::get('/pengumuman/{id}', [WargaBiasaPengumumanApiController::class, 'show'])
            ->name('pengumuman.show');

        // 4. Jadwal Ronda (R)
        Route::get('/jadwal-ronda', [WargaBiasaJadwalRondaApiController::class, 'index'])
            ->name('jadwal-ronda.index');
        Route::get('/jadwal-ronda/{id}', [WargaBiasaJadwalRondaApiController::class, 'show'])
            ->name('jadwal-ronda.show');

        // 5. Kegiatan (R)
        Route::get('/kegiatan', [WargaBiasaKegiatanApiController::class, 'index'])
            ->name('kegiatan.index');
        Route::get('/kegiatan/{id}', [WargaBiasaKegiatanApiController::class, 'show'])
            ->name('kegiatan.show');
    };

    Route::middleware('role:warga_biasa')
        ->prefix('warga-biasa')
        ->name('api.warga-biasa.')
        ->group($ruteWargaBiasa);

    Route::middleware('role:warga_biasa')
        ->prefix('warga')
        ->name('api.warga.')
        ->group($ruteWargaBiasa);
});
