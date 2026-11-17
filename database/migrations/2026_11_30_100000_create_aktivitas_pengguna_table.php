<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Catatan aktivitas asatid & siswa (masuk/keluar + semua simpan/ubah/hapus), tampil di lonceng admin
 * dan di halaman Sistem > Aktivitas Pengguna. Lihat App\Models\AktivitasPengguna & Middleware\CatatAktivitas.
 * Disimpan di database UTAMA (koneksi "pusat") karena akun dipakai bersama semua sesi; kolom "sesi" = nama sesi saat itu.
 * Migrasi ini ikut dijalankan di database sesi (php artisan sesi:migrate), jadi tabel hanya dibuat bila belum ada.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('pusat')->hasTable('aktivitas_pengguna')) {
            return;
        }

        Schema::connection('pusat')->create('aktivitas_pengguna', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('peran', 20);             // asatid | siswa
            $t->string('nama', 150)->nullable(); // nama akun saat itu (tetap terbaca bila akun dihapus)
            $t->string('aksi', 255);             // mis. "masuk (login)", "menyimpan absensi kelas"
            $t->string('url', 500)->nullable();
            $t->string('ip', 45)->nullable();
            $t->string('sesi', 100)->nullable();
            $t->timestamp('created_at')->nullable();

            $t->index(['peran', 'created_at']);
            $t->index('created_at');
        });
    }

    public function down(): void
    {
        // Hanya dihapus dari database utama
        if (DB::connection()->getDatabaseName() === DB::connection('pusat')->getDatabaseName()) {
            Schema::connection('pusat')->dropIfExists('aktivitas_pengguna');
        }
    }
};
