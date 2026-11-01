<?php

/*
|--------------------------------------------------------------------------
| Route Master Data (file terpisah, tidak di web.php)
|--------------------------------------------------------------------------
| Didaftarkan di bootstrap/app.php lewat `then:` dengan middleware 'web'.
| URL: /admin/master-data, /admin/master-data?tab=kamar, dst.
*/

use App\Http\Controllers\Admin\MasterDataController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin/master-data')
    ->name('admin.master-data.')
    ->middleware(['auth', 'peran:admin'])
    ->controller(MasterDataController::class)
    ->group(function () {
        // Halaman
        Route::get('/', 'index')->name('index');

        // Endpoint JSON yang dipanggil Vue
        Route::prefix('api/{type}')
            ->whereIn('type', array_keys(MasterDataController::TYPES))
            ->name('api.')
            ->group(function () {
                Route::get('/', 'list')->name('list');
                Route::post('/', 'store')->name('store');
                Route::post('reorder', 'reorder')->name('reorder');
                Route::put('{id}', 'update')->whereNumber('id')->name('update');
                Route::patch('{id}/toggle', 'toggle')->whereNumber('id')->name('toggle');
                Route::delete('{id}', 'destroy')->whereNumber('id')->name('destroy');
            });
    });
