<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kalender Pendidikan (Admin > Akademik > Kalender Pendidikan).
 * Satu baris = satu kegiatan (pembelajaran, ujian, libur, hari besar, ...) dari tanggal mulai sampai tanggal selesai.
 * Tanggal disimpan Masehi; tanggal Hijriyah dihitung otomatis saat ditampilkan (KalenderPendidikan::hijriyah).
 * Data milik sesi (angkatan), jadi ikut dijalankan di database tiap sesi (php artisan sesi:migrate).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('kalender_pendidikan')) {
            return;
        }

        Schema::create('kalender_pendidikan', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 150);
            $table->string('jenis', 30)->default('kegiatan');   // kunci KalenderPendidikan::JENIS
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->text('keterangan')->nullable();
            $table->unsignedBigInteger('dibuat_oleh')->nullable(); // id user admin (tanpa foreign key: akun bisa dari database lain)
            $table->timestamps();
            $table->index(['tanggal_mulai', 'tanggal_selesai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kalender_pendidikan');
    }
};
