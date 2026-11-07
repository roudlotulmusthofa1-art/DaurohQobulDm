<?php

use App\Http\Controllers\Asatid\AbsensiController;
use App\Http\Controllers\Asatid\AsatidDashboardController;
use App\Http\Controllers\Asatid\PendaftaranAsatidController;
use App\Http\Controllers\Asatid\PenilaianController;
use Illuminate\Support\Facades\Route;

/* Dimuat dari routes/web.php:  require __DIR__ . '/asatid.php'; */

// ===== PUBLIK: formulir pendaftaran asatid (hanya lewat link khusus dari admin) =====
Route::prefix('asatid')->name('asatid.')->group(function () {
    Route::get('/daftar/{kode}', [PendaftaranAsatidController::class, 'create'])->name('daftar');
    Route::post('/daftar/{kode}', [PendaftaranAsatidController::class, 'store'])->middleware('throttle:20,1')->name('store');
    Route::get('/sukses/{nomor}', [PendaftaranAsatidController::class, 'sukses'])->name('sukses');
});

// ===== ASATID: akun asatid (login lewat halaman login yang sama) =====
Route::prefix('asatid')->name('asatid.')->middleware(['auth', 'peran:asatid'])->group(function () {
    Route::get('/beranda', [AsatidDashboardController::class, 'index'])->name('dashboard');
    Route::get('/profil', [AsatidDashboardController::class, 'profil'])->name('profil');
    Route::get('/ganti-password', [AsatidDashboardController::class, 'gantiPassword'])->name('ganti-password');
    Route::post('/ganti-password', [AsatidDashboardController::class, 'simpanGantiPassword'])->middleware('throttle:10,1')->name('ganti-password.simpan');
    Route::get('/jadwal', [AsatidDashboardController::class, 'jadwal'])->name('jadwal');
    // Penilaian: menu + Nilai Harian/Ujian Lisan/Ujian (menyusul) + Catatan (pencapaian / pelanggaran)
    Route::controller(PenilaianController::class)->prefix('penilaian')->group(function () {
        Route::get('/', 'index')->name('penilaian');
        Route::get('/kelas/{bagian}', 'kelas')->whereIn('bagian', PenilaianController::BAGIAN_KELAS)->name('penilaian.kelas');
        Route::get('/catatan', 'catatan')->name('penilaian.catatan');
        Route::post('/catatan', 'simpan')->middleware('throttle:60,1')->name('penilaian.catatan.simpan');
        Route::delete('/catatan/{id}', 'hapus')->whereNumber('id')->name('penilaian.catatan.hapus');
    });
    Route::get('/pesan', [AsatidDashboardController::class, 'pesan'])->name('pesan'); // Pesan dari Admin
    Route::get('/pesan-saya', [AsatidDashboardController::class, 'pesanSaya'])->name('pesan-saya'); // Pesan untuk Admin
    Route::post('/pesan-saya', [AsatidDashboardController::class, 'kirimPesan'])->middleware('throttle:10,1')->name('pesan-saya.kirim');
    Route::get('/peserta', [AsatidDashboardController::class, 'peserta'])->name('peserta');
    Route::get('/peserta/{siswa}', [AsatidDashboardController::class, 'pesertaShow'])->whereNumber('siswa')->name('peserta.show');

    // Absensi peserta (kelas & kegiatan)
    Route::controller(AbsensiController::class)->prefix('absensi')->group(function () {
        Route::get('/', 'index')->name('absensi');
        Route::get('/kelas', 'kelas')->name('absensi.kelas');
        Route::get('/kelas/peserta', 'pesertaKelas')->name('absensi.kelas.peserta');
        Route::post('/kelas', 'simpanKelas')->name('absensi.kelas.simpan');
        Route::get('/kegiatan', 'kegiatan')->name('absensi.kegiatan');
        Route::post('/kegiatan', 'mulaiKegiatan')->name('absensi.kegiatan.mulai');
        Route::get('/kegiatan/{absensi}', 'catatKegiatan')->name('absensi.kegiatan.catat');
        Route::post('/kegiatan/{absensi}/catat', 'simpanCatatan')->name('absensi.kegiatan.simpan');
        Route::get('/kegiatan/{absensi}/status', 'statusKegiatan')->name('absensi.kegiatan.status'); // pembaruan otomatis (dicatat bersama)
        Route::post('/kegiatan/{absensi}/selesai', 'selesaiKegiatan')->name('absensi.kegiatan.selesai');
        Route::get('/{absensi}', 'show')->whereNumber('absensi')->name('absensi.show');
        Route::delete('/{absensi}', 'destroy')->whereNumber('absensi')->name('absensi.destroy');
    });

    Route::post('/keluar', [AsatidDashboardController::class, 'logout'])->name('logout');
});
