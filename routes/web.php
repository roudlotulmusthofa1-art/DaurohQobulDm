<?php

use App\Http\Controllers\Admin\AdminDataAsatidController;
use App\Http\Controllers\Admin\AdminDataAdminController;
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
    // Data Asatid: daftar, detail, terima/tolak, hapus, dan link pendaftaran khusus
    Route::post('asatid/link-baru', [AdminDataAsatidController::class, 'linkBaru'])->name('asatid.link-baru');
    Route::post('asatid/{asatid}/terima', [AdminDataAsatidController::class, 'terima'])->name('asatid.terima');
    Route::post('asatid/{asatid}/tolak', [AdminDataAsatidController::class, 'tolak'])->name('asatid.tolak');
    Route::resource('asatid', AdminDataAsatidController::class)->only(['index', 'show', 'destroy']);
    // Data Admin: sama seperti Data Asatid, akun yang diterima ber-role admin
    Route::post('data-admin/link-baru', [AdminDataAdminController::class, 'linkBaru'])->name('data-admin.link-baru');
    Route::post('data-admin/{dataAdmin}/terima', [AdminDataAdminController::class, 'terima'])->name('data-admin.terima');
    Route::post('data-admin/{dataAdmin}/tolak', [AdminDataAdminController::class, 'tolak'])->name('data-admin.tolak');
    Route::resource('data-admin', AdminDataAdminController::class)->only(['index', 'show', 'destroy'])
        ->parameters(['data-admin' => 'dataAdmin']);

    // ===== Akademik =====
    // Tahun & Bulan Ajaran
    Route::controller(\App\Http\Controllers\Admin\TahunAjaranController::class)->group(function () {
        Route::get('tahun-ajaran', 'index')->name('tahun-ajaran.index');
        Route::post('tahun-ajaran', 'store')->name('tahun-ajaran.store');
        Route::put('tahun-ajaran/{tahunAjaran}', 'update')->name('tahun-ajaran.update');
        Route::delete('tahun-ajaran/{tahunAjaran}', 'destroy')->name('tahun-ajaran.destroy');
        Route::post('tahun-ajaran/{tahunAjaran}/aktifkan', 'aktifkan')->name('tahun-ajaran.aktifkan');
        Route::post('tahun-ajaran/{tahunAjaran}/isi-bulan', 'isiBulan')->name('tahun-ajaran.isi-bulan');
        Route::post('tahun-ajaran/{tahunAjaran}/bulan', 'storeBulan')->name('bulan-ajaran.store');
        Route::put('bulan-ajaran/{bulanAjaran}', 'updateBulan')->name('bulan-ajaran.update');
        Route::delete('bulan-ajaran/{bulanAjaran}', 'destroyBulan')->name('bulan-ajaran.destroy');
        Route::post('bulan-ajaran/{bulanAjaran}/aktifkan', 'aktifkanBulan')->name('bulan-ajaran.aktifkan');
    });
    // Kurikulum & Kitab
    Route::resource('kurikulum', \App\Http\Controllers\Admin\KurikulumController::class)->only(['index', 'store', 'update', 'destroy']);
    // Jadwal Mengajar
    Route::post('jadwal/salin', [\App\Http\Controllers\Admin\JadwalMengajarController::class, 'salin'])->name('jadwal.salin');
    Route::post('jadwal/ganti-ustadz', [\App\Http\Controllers\Admin\JadwalMengajarController::class, 'gantiUstadz'])->name('jadwal.ganti-ustadz');
    Route::resource('jadwal', \App\Http\Controllers\Admin\JadwalMengajarController::class)->only(['index', 'store', 'update', 'destroy']);

    // Landing Page > Hero & Statistik (angka di bagian atas beranda)
    Route::get('landing-hero', [\App\Http\Controllers\Admin\LandingHeroController::class, 'index'])->name('landing-hero.index');
    // Laporan > Laporan Absensi (hasil absensi kelas & kegiatan dari asatid)
    Route::get('laporan-absensi', [\App\Http\Controllers\Admin\LaporanAbsensiController::class, 'index'])->name('laporan-absensi.index');
    Route::get('laporan-absensi/{absensi}', [\App\Http\Controllers\Admin\LaporanAbsensiController::class, 'show'])->name('laporan-absensi.show');
    Route::delete('laporan-absensi/{absensi}', [\App\Http\Controllers\Admin\LaporanAbsensiController::class, 'destroy'])->name('laporan-absensi.destroy');

    // Sistem > Pengaturan (termasuk pengaturan notifikasi)
    Route::get('pengaturan', [\App\Http\Controllers\Admin\PengaturanController::class, 'index'])->name('pengaturan.index');
    Route::put('pengaturan', [\App\Http\Controllers\Admin\PengaturanController::class, 'update'])->name('pengaturan.update');
    // Route::resource('wali', AdminWaliController::class);
});



require __DIR__ . '/psb.php';
require __DIR__ . '/asatid.php';

// Formulir pendaftaran admin (publik, hanya bisa dibuka lewat link khusus dari halaman Data Admin)
Route::get('daftar-admin/{kode}', [\App\Http\Controllers\PendaftaranAdminController::class, 'create'])->name('pendaftaran-admin.daftar');
Route::post('daftar-admin/{kode}', [\App\Http\Controllers\PendaftaranAdminController::class, 'store'])->middleware('throttle:20,1')->name('pendaftaran-admin.store');
Route::get('daftar-admin-sukses/{nomor}', [\App\Http\Controllers\PendaftaranAdminController::class, 'sukses'])->name('pendaftaran-admin.sukses');
