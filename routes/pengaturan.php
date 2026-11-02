<?php

use App\Http\Controllers\Admin\PengaturanController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Route Sistem > Pengaturan (file terpisah)
|--------------------------------------------------------------------------
| Dimuat dari routes/web.php:  require __DIR__ . '/pengaturan.php';
| URL: /admin/pengaturan?tab=umum | profil | notifikasi | database | sesi
*/

Route::prefix('admin/pengaturan')->name('admin.pengaturan.')->middleware(['auth', 'peran:admin'])
    ->controller(PengaturanController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/', 'update')->name('update');              // tab Notifikasi
        Route::put('umum', 'simpanUmum')->name('umum');         // tab Umum: profil pesantren
        Route::delete('logo', 'hapusLogo')->name('logo.hapus'); // tab Umum: hapus logo
        Route::put('profil', 'simpanProfil')->name('profil');   // tab Profil: akun admin
        Route::put('pesan', 'simpanPesan')->name('pesan');      // tab Pesan: template WhatsApp

        // tab Database (berlaku untuk sesi yang sedang aktif)
        Route::get('database/backup', 'backup')->name('backup');                       // unduh langsung
        Route::post('database/backup', 'simpanBackup')->name('backup.simpan');         // simpan di server
        Route::get('database/backup/{nama}', 'unduhBackup')->name('backup.unduh');
        Route::delete('database/backup/{nama}', 'hapusBackup')->name('backup.hapus');
        Route::post('database/restore', 'restore')->name('restore');
        Route::post('database/hapus-data', 'hapusData')->name('hapus-data');

        // tab Sesi (angkatan/gelombang dauroh)
        Route::post('sesi', 'simpanSesi')->name('sesi.simpan');
        Route::put('sesi/{sesi}', 'ubahSesi')->name('sesi.ubah');
        Route::post('sesi/{sesi}/aktifkan', 'aktifkanSesi')->name('sesi.aktifkan');
        Route::delete('sesi/{sesi}', 'hapusSesi')->name('sesi.hapus');
    });
