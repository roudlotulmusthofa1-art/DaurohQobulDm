<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dua menu baru di Master Data:
 * - jenis_pelanggaran : daftar jenis pelanggaran santri
 * - jenis_khidmah     : daftar tugas khidmah/pengabdian santri (mis. Kebersihan, Dapur)
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('jenis_pelanggaran')) {
            Schema::create('jenis_pelanggaran', function (Blueprint $table) {
                $table->id();
                $table->string('nama', 100);
                $table->string('warna', 30)->nullable(); // class Tailwind, mis. bg-red-100
                $table->text('deskripsi')->nullable();
                $table->unsignedInteger('urutan')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

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
        Schema::dropIfExists('jenis_pelanggaran');
    }
};
