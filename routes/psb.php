<?php

use App\Http\Controllers\Admin\AdminPendaftaranController;
use App\Http\Controllers\Psb\PendaftaranController;
use App\Http\Controllers\Siswa\SiswaProfilController;
use Illuminate\Support\Facades\Route;

/* Tambahkan di routes/web.php:  require __DIR__ . '/psb.php'; */

// ===== PUBLIK: formulir pendaftaran 5 step =====
Route::prefix('psb')->name('psb.')->group(function () {
    Route::get('/daftar', [PendaftaranController::class, 'create'])->name('daftar');
    Route::post('/daftar', [PendaftaranController::class, 'store'])->middleware('throttle:60,1')->name('store');
    Route::get('/sukses/{nomor}', [PendaftaranController::class, 'sukses'])->name('sukses');
});

// ===== ADMIN: kelola pendaftar =====
// ===== ADMIN =====
Route::prefix('admin')->middleware(['auth', 'peran:admin'])->group(function () {

    // Dua menu di sidebar (sesuai AppServiceProvider)
    Route::get('data-masuk', [AdminPendaftaranController::class, 'index'])
        ->name('admin.data-masuk.index');

    Route::get('data-diterima', [AdminPendaftaranController::class, 'index'])
        ->defaults('status', 'diterima')
        ->name('admin.data-diterima.index');

    // Detail dan aksi, dipakai oleh tombol di view
    Route::prefix('pendaftaran')->name('admin.pendaftaran.')->group(function () {
        Route::get('/', [AdminPendaftaranController::class, 'index'])->name('index');
        Route::get('/{pendaftaran}', [AdminPendaftaranController::class, 'show'])->name('show');
        Route::post('/{pendaftaran}/terima', [AdminPendaftaranController::class, 'terima'])->name('terima');
        Route::post('/{pendaftaran}/tolak', [AdminPendaftaranController::class, 'tolak'])->name('tolak');
        Route::delete('/{pendaftaran}', [AdminPendaftaranController::class, 'destroy'])->name('destroy');
    });
});

// ===== SISWA: akun siswa (login lewat halaman login yang sama dengan admin) =====
Route::prefix('siswa')->name('siswa.')->middleware(['auth', 'peran:siswa'])->group(function () {
    Route::get('/profil', [SiswaProfilController::class, 'index'])->name('profil');
    Route::post('/keluar', [SiswaProfilController::class, 'logout'])->name('logout');
});




