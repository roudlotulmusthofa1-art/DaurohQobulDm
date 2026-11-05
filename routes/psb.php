<?php

use App\Http\Controllers\Admin\AdminPendaftaranController;
use App\Http\Controllers\Psb\PendaftaranController;
use App\Http\Controllers\Siswa\SiswaDashboardController;
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

    // Menu sidebar PSB > Data Masuk Peserta (sesuai AppServiceProvider)
    Route::get('data-masuk', [AdminPendaftaranController::class, 'index'])
        ->name('admin.data-masuk.index');

    // Detail dan aksi, dipakai oleh tombol di view
    Route::prefix('pendaftaran')->name('admin.pendaftaran.')->group(function () {
        Route::get('/', [AdminPendaftaranController::class, 'index'])->name('index');
        Route::post('/buka-tutup', [AdminPendaftaranController::class, 'bukaTutup'])->name('buka-tutup'); // Aktif/Nonaktif pendaftaran
        Route::get('/{pendaftaran}', [AdminPendaftaranController::class, 'show'])->name('show');
        Route::post('/{pendaftaran}/terima', [AdminPendaftaranController::class, 'terima'])->name('terima');
        Route::post('/{pendaftaran}/tolak', [AdminPendaftaranController::class, 'tolak'])->name('tolak');
        Route::post('/{pendaftaran}/kirim-ulang-chat', [AdminPendaftaranController::class, 'kirimUlangChat'])->name('kirim-ulang-chat');
        Route::post('/{pendaftaran}/reset-password', [AdminPendaftaranController::class, 'resetPassword'])->name('reset-password');
        Route::delete('/{pendaftaran}', [AdminPendaftaranController::class, 'destroy'])->name('destroy');
        Route::get('/{pendaftaran}/edit', [AdminPendaftaranController::class, 'edit'])->name('edit');
        Route::put('/{pendaftaran}', [AdminPendaftaranController::class, 'update'])->name('update');
        Route::post('/{pendaftaran}/syarat', [AdminPendaftaranController::class, 'simpanSyarat'])->name('syarat');
    });
});

// ===== ADMIN: Pesan Peserta (pesan dua arah dengan dasbor siswa) =====
Route::prefix('admin/pesan-peserta')->name('admin.pesan-peserta.')->middleware(['auth', 'peran:admin'])
    ->controller(\App\Http\Controllers\Admin\PesanPesertaController::class)->group(function () {
        Route::get('/', 'index')->name('index');                          // ?asal=siswa|asatid|umum
        Route::post('/', 'kirim')->name('kirim');                         // pesan baru (satu / banyak peserta)
        // Pesan dari asatid (dasbor asatid > Pesan untuk Admin)
        Route::get('/asatid/{user}', 'showAsatid')->whereNumber('user')->name('asatid.show');
        Route::post('/asatid/{user}/balas', 'balasAsatid')->whereNumber('user')->name('asatid.balas');
        Route::delete('/asatid/pesan/{pesan}', 'hapusAsatid')->name('asatid.hapus');
        // Pesan pengunjung umum (landing page > Hubungi Admin)
        Route::post('/umum/{pesan}/baca', 'bacaUmum')->name('umum.baca');
        Route::delete('/umum/{pesan}', 'hapusUmum')->name('umum.hapus');
        Route::get('/{peserta}', 'show')->name('show');                   // percakapan dengan satu peserta
        Route::post('/{peserta}/balas', 'balas')->name('balas');
        Route::delete('/pesan/{pesan}', 'hapus')->name('hapus');
    });

// ===== SISWA: akun siswa (login lewat halaman login yang sama dengan admin) =====
Route::prefix('siswa')->name('siswa.')->middleware(['auth', 'peran:siswa'])->group(function () {
    // Dasbor siswa: kartu menu Profil, Pengumuman Kelulusan, Pesan dari Admin, Pesan untuk Admin
    Route::get('/beranda', [SiswaDashboardController::class, 'index'])->name('dashboard');
    Route::get('/profil', [SiswaDashboardController::class, 'profil'])->name('profil');
    Route::get('/ganti-password', [SiswaDashboardController::class, 'gantiPassword'])->name('ganti-password');
    Route::post('/ganti-password', [SiswaDashboardController::class, 'simpanGantiPassword'])->middleware('throttle:10,1')->name('ganti-password.simpan');
    Route::get('/kelulusan', [SiswaDashboardController::class, 'kelulusan'])->name('kelulusan');
    Route::get('/pesan-admin', [SiswaDashboardController::class, 'pesanAdmin'])->name('pesan-admin');
    Route::get('/pesan-saya', [SiswaDashboardController::class, 'pesanSaya'])->name('pesan-saya');
    Route::post('/pesan-saya', [SiswaDashboardController::class, 'kirimPesan'])->middleware('throttle:10,1')->name('pesan-saya.kirim');
    Route::post('/keluar', [SiswaDashboardController::class, 'logout'])->name('logout');
});




