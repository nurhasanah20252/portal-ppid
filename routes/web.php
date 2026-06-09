<?php

use Illuminate\Support\Facades\Route;

// ============================================================
// Halaman Publik (tanpa autentikasi)
// ============================================================

/** Beranda */
Route::inertia('/', 'public/home')->name('home');

/** Profil PPID */
Route::inertia('/profil', 'public/profil')->name('profil');

/** Informasi Publik */
Route::inertia('/informasi-publik', 'public/informasi-publik')->name('informasi-publik');

/** Form Permohonan Informasi */
Route::inertia('/permohonan', 'public/permohonan')->name('permohonan.create');

/** Cek Status Permohonan */
Route::inertia('/status', 'public/status')->name('status.index');

/** Form Keberatan */
Route::inertia('/keberatan', 'public/keberatan')->name('keberatan.create');

/** FAQ */
Route::inertia('/faq', 'public/faq')->name('faq.index');

/** Kontak */
Route::inertia('/kontak', 'public/kontak')->name('kontak.index');

// ============================================================
// Halaman Autentikasi (admin)
// ============================================================

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
