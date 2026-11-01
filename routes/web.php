<?php

use App\Http\Controllers\Admin\AdminDataAsatidController;
use App\Http\Controllers\Admin\AdminDataKelasController;
use App\Http\Controllers\Admin\AdminSantriController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Route;


Route::get('/', [LandingController::class, 'index'])->name('landing');

// route untuk Masuk/Login

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'index'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.store');
});


Route::prefix('admin')->name('admin.')->middleware(['auth', 'peran:admin'])->group(function () {
    Route::get('beranda', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('logout', [\App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

    // Manajemen Data
    Route::post('santri/massal', [AdminSantriController::class, 'massal'])->name('santri.massal'); // tombol Kelola Massal
    Route::resource('santri', AdminSantriController::class);
    Route::resource('asatid', AdminDataAsatidController::class);
    Route::resource('kelas', AdminDataKelasController::class);

    // Landing Page > Hero & Statistik (angka di bagian atas beranda)
    Route::get('landing-hero', [\App\Http\Controllers\Admin\LandingHeroController::class, 'index'])->name('landing-hero.index');
    Route::put('landing-hero', [\App\Http\Controllers\Admin\LandingHeroController::class, 'update'])->name('landing-hero.update');

    // Sistem > Pengaturan (termasuk pengaturan notifikasi)
    Route::get('pengaturan', [\App\Http\Controllers\Admin\PengaturanController::class, 'index'])->name('pengaturan.index');
    Route::put('pengaturan', [\App\Http\Controllers\Admin\PengaturanController::class, 'update'])->name('pengaturan.update');
    // Route::resource('wali', AdminWaliController::class);
});



require __DIR__ . '/psb.php';
