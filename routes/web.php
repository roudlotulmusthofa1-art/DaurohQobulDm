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


Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::get('beranda', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('logout', [\App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

    // Manajemen Data
    Route::resource('santri', AdminSantriController::class);
    Route::resource('asatid', AdminDataAsatidController::class);
    Route::resource('kelas', AdminDataKelasController::class);
    // Route::resource('wali', AdminWaliController::class);
});



