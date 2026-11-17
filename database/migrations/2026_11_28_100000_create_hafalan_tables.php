<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hafalan (Admin > Akademik > Hafalan, Asatid > Hafalan).
 * - hafalan         : satu baris = satu setoran hafalan santri untuk satu kitab
 *                     (tanggal, halaman dari-sampai, berapa kali ragu-ragu, berapa kesalahan, nilai pilihan dari Pengaturan).
 * - hafalan_selesai : tanda santri sudah hafal SELURUH kitab itu (satu santri + kitab = satu baris).
 * Pengaturan (kitab yang wajib dihafal, pilihan nilai) disimpan di tabel pengaturan: hafalan_kitab, hafalan_nilai.
 * Data milik sesi (angkatan), jadi ikut dijalankan di database tiap sesi (php artisan sesi:migrate).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('hafalan')) {
            Schema::create('hafalan', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pendaftaran_siswa_id')->constrained('pendaftaran_siswas')->cascadeOnDelete();
                $table->foreignId('kitab_id')->constrained('kitab')->cascadeOnDelete();
                $table->date('tanggal');
                $table->unsignedSmallInteger('halaman_dari');
                $table->unsignedSmallInteger('halaman_sampai');
                $table->unsignedSmallInteger('ragu')->default(0);   // berapa kali ragu-ragu
                $table->unsignedSmallInteger('salah')->default(0);  // berapa kesalahan
                $table->string('nilai', 100);                        // salah satu pilihan nilai dari Pengaturan
                $table->foreignId('dinilai_oleh')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->index(['pendaftaran_siswa_id', 'kitab_id', 'tanggal']);
            });
        }

        if (! Schema::hasTable('hafalan_selesai')) {
            Schema::create('hafalan_selesai', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pendaftaran_siswa_id')->constrained('pendaftaran_siswas')->cascadeOnDelete();
                $table->foreignId('kitab_id')->constrained('kitab')->cascadeOnDelete();
                $table->foreignId('ditandai_oleh')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('ditandai_pada')->nullable();      // jam WIB
                $table->timestamps();
                $table->unique(['pendaftaran_siswa_id', 'kitab_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hafalan_selesai');
        Schema::dropIfExists('hafalan');
    }
};
