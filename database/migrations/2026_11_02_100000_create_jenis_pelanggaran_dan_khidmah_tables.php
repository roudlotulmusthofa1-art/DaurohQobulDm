<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menu baru di Master Data:
 * - jenis_khidmah : daftar tugas khidmah/pengabdian santri (mis. Kebersihan, Dapur)
 * (Jenis Pelanggaran dulu juga dibuat di sini; sudah dihapus, lihat 2026_11_14_130000_hapus_jenis_pelanggaran_pencapaian.)
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('jenis_khidmah')) {
            Schema::create('jenis_khidmah', function (Blueprint $table) {
                $table->id();
                $table->string('nama', 100);
                $table->string('warna', 30)->nullable(); // class Tailwind, mis. bg-sky-100
                $table->text('deskripsi')->nullable();
                $table->unsignedInteger('urutan')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_khidmah');
    }
};
