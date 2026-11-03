<?php

use App\Http\Controllers\Admin\PitaPengumumanController;
use Illuminate\Support\Facades\Route;

/*
| Admin > Landing Page > Pita Pengumuman
| URL: /admin/landing/pita-pengumuman
*/
Route::prefix('admin/landing/pita-pengumuman')->name('admin.landing-pengumuman.')->middleware(['auth', 'peran:admin'])
    ->controller(PitaPengumumanController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::post('urutan', 'urutan')->name('urutan');
        Route::post('tampilan', 'tampilan')->name('tampilan'); // posisi & ukuran pita
        Route::put('{pita}', 'update')->name('update');
        Route::patch('{pita}/toggle', 'toggle')->name('toggle');
        Route::delete('{pita}', 'destroy')->name('destroy');
    });
