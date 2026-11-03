<?php

use App\Http\Controllers\Admin\DaftarUserController;
use App\Http\Controllers\Admin\MasukSebagaiController;
use App\Http\Controllers\Admin\RoleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Route Sistem > Role & Permission dan Sistem > Daftar User (file terpisah)
|--------------------------------------------------------------------------
| Dimuat dari routes/web.php:  require __DIR__ . '/sistem.php';
| Role & Permission hanya bisa dibuka Super Admin (lihat App\Support\MenuAdmin::KHUSUS_SUPER).
*/

Route::prefix('admin')->name('admin.')->middleware(['auth', 'peran:admin'])->group(function () {
    // Role & Permission
    Route::get('role', [RoleController::class, 'index'])->name('role.index');
    Route::post('role', [RoleController::class, 'store'])->name('role.store');
    Route::put('role/{role}', [RoleController::class, 'update'])->name('role.update');
    Route::delete('role/{role}', [RoleController::class, 'destroy'])->name('role.destroy');

    // Daftar User (semua akun beserta role-nya)
    Route::get('user', [DaftarUserController::class, 'index'])->name('akun.index');
    Route::put('user/{user}/role', [DaftarUserController::class, 'ubahRole'])->name('akun.role');

    // Masuk sebagai asatid/siswa (khusus Super Admin, hanya untuk melihat dasbor mereka)
    Route::post('masuk-sebagai/{user}', [MasukSebagaiController::class, 'mulai'])->name('masuk-sebagai');
});

// Kembali ke akun admin (dipanggil saat sedang "Masuk sebagai" -> role-nya asatid/siswa, jadi di luar grup admin)
Route::post('masuk-sebagai/selesai', [MasukSebagaiController::class, 'selesai'])->middleware('auth')->name('masuk-sebagai.selesai');
Route::post('masuk-sebagai/mode', [MasukSebagaiController::class, 'mode'])->middleware('auth')->name('masuk-sebagai.mode'); // izinkan perubahan / mode lihat
