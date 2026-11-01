<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dua menu baru di Master Data:
 * - jenis_kegiatan : jenis/nama kegiatan pondok (mis. Muhadharah, Ro'an, Ziarah)
 * - semester       : semester per tahun ajaran, dengan tanggal Masehi DAN Hijriyah
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('jenis_kegiatan')) {
            Schema::create('jenis_kegiatan', function (Blueprint $table) {
                $table->id();
                $table->string('nama', 100);
                $table->string('warna', 30)->nullable(); // class Tailwind, mis. bg-teal-100
                $table->text('deskripsi')->nullable();
                $table->unsignedInteger('urutan')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('semester')) {
            Schema::create('semester', function (Blueprint $table) {
                $table->id();
                $table->string('nama', 100);                          // mis. Semester Ganjil

                // Kalender Masehi
                $table->string('tahun_ajaran', 20)->nullable();       // mis. 2026/2027
                $table->date('tanggal_mulai')->nullable();            // mis. 2026-07-13
                $table->date('tanggal_selesai')->nullable();

                // Kalender Hijriyah (disimpan sebagai teks, mis. "27 Muharam 1448 H",
                // karena tanggal Hijriyah bisa berbeda 1 hari menurut rukyat)
                $table->string('tahun_ajaran_hijriyah', 30)->nullable(); // mis. 1447/1448 H
                $table->string('tanggal_mulai_hijriyah', 50)->nullable();
                $table->string('tanggal_selesai_hijriyah', 50)->nullable();

                $table->string('keterangan')->nullable();
                $table->unsignedInteger('urutan')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('semester');
        Schema::dropIfExists('jenis_kegiatan');
    }
};
