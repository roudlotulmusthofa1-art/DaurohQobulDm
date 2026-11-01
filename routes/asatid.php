<?php

use App\Http\Controllers\Asatid\AsatidDashboardController;
use App\Http\Controllers\Asatid\PendaftaranAsatidController;
use Illuminate\Support\Facades\Route;

/* Dimuat dari routes/web.php:  require __DIR__ . '/asatid.php'; */

// ===== PUBLIK: formulir pendaftaran asatid (hanya lewat link khusus dari admin) =====
Route::prefix('asatid')->name('asatid.')->group(function () {
    Route::get('/daftar/{kode}', [PendaftaranAsatidController::class, 'create'])->name('daftar');
    Route::post('/daftar/{kode}', [PendaftaranAsatidController::class, 'store'])->middleware('throttle:20,1')->name('store');
    Route::get('/sukses/{nomor}', [PendaftaranAsatidController::class, 'sukses'])->name('sukses');
});

// ===== ASATID: akun asatid (login lewat halaman login yang sama) =====
Route::prefix('asatid')->name('asatid.')->middleware(['auth', 'peran:asatid'])->group(function () {
    Route::get('/beranda', [AsatidDashboardController::class, 'index'])->name('dashboard');
    Route::get('/profil', [AsatidDashboardController::class, 'profil'])->name('profil');
    Route::get('/absensi', [AsatidDashboardController::class, 'absensi'])->name('absensi');
    Route::get('/jadwal', [AsatidDashboardController::class, 'jadwal'])->name('jadwal');
    Route::get('/penilaian', [AsatidDashboardController::class, 'penilaian'])->name('penilaian');
    Route::get('/peserta', [AsatidDashboardController::class, 'peserta'])->name('peserta');
    Route::post('/keluar', [AsatidDashboardController::class, 'logout'])->name('logout');
});
