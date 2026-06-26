<?php

use App\Http\Controllers\Api\V1\AdminFaqController;
use App\Http\Controllers\Api\V1\AdminInformasiPublikController;
use App\Http\Controllers\Api\V1\AdminKeberatanController;
use App\Http\Controllers\Api\V1\AdminLaporanController;
use App\Http\Controllers\Api\V1\AdminPermohonanController;
use App\Http\Controllers\Api\V1\AdminStatistikController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\FaqController;
use App\Http\Controllers\Api\V1\InformasiPublikController;
use App\Http\Controllers\Api\V1\KeberatanController;
use App\Http\Controllers\Api\V1\PermohonanController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Semua route di file ini otomatis mendapat prefix /api.
| Route dikelompokkan dengan prefix v1 untuk versioning.
| Route admin diproteksi dengan middleware auth:sanctum dan log.admin.
|
*/

Route::prefix('v1')->group(function (): void {
    // === Auth routes ===
    Route::prefix('auth')->group(function (): void {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/logout', [AuthController::class, 'logout'])
            ->middleware('auth:sanctum');
    });

    // === Public routes (tanpa auth) ===
    Route::post('/permohonan', [PermohonanController::class, 'store'])
        ->middleware('throttle:permohonan');
    Route::get('/permohonan/{tiket_no}', [PermohonanController::class, 'show']);
    Route::post('/keberatan', [KeberatanController::class, 'store']);
    Route::get('/informasi-publik', [InformasiPublikController::class, 'index']);
    Route::get('/informasi-publik/{id}/download', [InformasiPublikController::class, 'download']);
    Route::get('/faq', [FaqController::class, 'index']);

    // === Admin routes (auth:sanctum + log.admin) ===
    Route::prefix('admin')->middleware(['auth:sanctum', 'log.admin'])->group(function (): void {
        // Permohonan management
        Route::get('/permohonan', [AdminPermohonanController::class, 'index']);
        Route::get('/permohonan/{tiket_no}', [AdminPermohonanController::class, 'show']);
        Route::put('/permohonan/{tiket_no}/status', [AdminPermohonanController::class, 'updateStatus']);
        Route::post('/permohonan/{tiket_no}/dokumen', [AdminPermohonanController::class, 'uploadDokumen']);

        // Keberatan management
        Route::get('/keberatan', [AdminKeberatanController::class, 'index']);
        Route::put('/keberatan/{keberatan}', [AdminKeberatanController::class, 'update']);

        // Informasi Publik CRUD
        Route::post('/informasi-publik', [AdminInformasiPublikController::class, 'store']);
        Route::put('/informasi-publik/{informasiPublik}', [AdminInformasiPublikController::class, 'update']);
        Route::delete('/informasi-publik/{informasiPublik}', [AdminInformasiPublikController::class, 'destroy']);

        // FAQ CRUD
        Route::get('/faq', [AdminFaqController::class, 'index']);
        Route::post('/faq', [AdminFaqController::class, 'store']);
        Route::put('/faq/{faq}', [AdminFaqController::class, 'update']);
        Route::delete('/faq/{faq}', [AdminFaqController::class, 'destroy']);

        // Statistik
        Route::get('/statistik', [AdminStatistikController::class, 'index']);

        // Laporan
        Route::get('/laporan/permohonan', [AdminLaporanController::class, 'permohonan']);
    });
});
