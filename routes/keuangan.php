<?php

use App\Http\Controllers\Admin\BukuKasController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Route Keuangan (file terpisah)
|--------------------------------------------------------------------------
| Dimuat dari routes/web.php:  require __DIR__ . '/keuangan.php';
| URL: /admin/keuangan/buku-kas?start_date=2026-10-04&end_date=2026-11-02&tipe=masuk&kategori=1
*/

Route::prefix('admin/keuangan/buku-kas')->name('admin.buku-kas.')->middleware(['auth', 'peran:admin'])
    ->controller(BukuKasController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::put('{transaksi}', 'update')->name('update');
        Route::delete('{transaksi}', 'destroy')->name('destroy');

        // Kelola Kategori
        Route::post('kategori', 'simpanKategori')->name('kategori.store');
        Route::put('kategori/{kategori}', 'ubahKategori')->name('kategori.update');
        Route::delete('kategori/{kategori}', 'hapusKategori')->name('kategori.destroy');
    });
