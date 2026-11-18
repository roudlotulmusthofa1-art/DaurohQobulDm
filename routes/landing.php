<?php

use App\Http\Controllers\Admin\PitaPengumumanController;
use Illuminate\Support\Facades\Route;

/*
| Admin > Landing Page > Galeri Foto (album admin + Kotak Masuk kiriman asatid & siswa)
| URL: /admin/landing/galeri
*/
Route::prefix('admin/landing/galeri')->name('admin.galeri.')->middleware(['auth', 'peran:admin'])
    ->controller(\App\Http\Controllers\Admin\GaleriController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'unggah')->name('unggah');
        Route::post('{album}', 'ubah')->whereNumber('album')->name('ubah');            // POST (bukan PUT) karena membawa file foto
        Route::post('{album}/tampilkan', 'tampilkan')->whereNumber('album')->name('tampilkan');
        Route::post('{album}/tolak', 'tolak')->whereNumber('album')->name('tolak');
        Route::delete('{album}', 'hapus')->whereNumber('album')->name('hapus');
        Route::patch('berita/{foto}', 'sembunyikan')->whereNumber('foto')->name('sembunyikan'); // foto berita: sembunyikan / tampilkan
    });

/*
| Admin > Landing Page > Berita
| URL: /admin/landing/berita   (tulisan asatid & siswa masuk ke Kotak Masuk di sini)
*/
Route::prefix('admin/landing/berita')->name('admin.berita.')->middleware(['auth', 'peran:admin'])
    ->controller(\App\Http\Controllers\Admin\BeritaController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::post('{berita:id}', 'update')->name('update');            // POST (bukan PUT) karena membawa file foto
        Route::post('{berita:id}/tampilkan', 'tampilkan')->name('tampilkan');
        Route::post('{berita:id}/turunkan', 'turunkan')->name('turunkan');
        Route::post('{berita:id}/tolak', 'tolak')->name('tolak');
        Route::delete('{berita:id}', 'destroy')->name('destroy');
        Route::get('{berita:id}/komentar', 'komentar')->name('komentar');
        Route::delete('komentar/{komentar}', 'hapusKomentar')->name('komentar.hapus');
    });

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
        Route::post('jenis', 'tambahJenis')->name('jenis.tambah');      // jenis pengumuman buatan admin
        Route::post('jenis/hapus', 'hapusJenis')->name('jenis.hapus');
        Route::put('{pita}', 'update')->name('update');
        Route::patch('{pita}/toggle', 'toggle')->name('toggle');
        Route::delete('{pita}', 'destroy')->name('destroy');
    });
