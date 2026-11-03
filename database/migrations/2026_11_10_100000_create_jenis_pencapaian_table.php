<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menu baru di Master Data: jenis_pencapaian
 * (daftar jenis prestasi/pencapaian santri, mis. Hafal Juz 30, Juara Lomba).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('jenis_pencapaian')) {
            Schema::create('jenis_pencapaian', function (Blueprint $table) {
                $table->id();
                $table->string('nama', 100);
                $table->string('warna', 30)->nullable(); // class Tailwind, mis. bg-emerald-100
                $table->text('deskripsi')->nullable();
                $table->unsignedInteger('urutan')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_pencapaian');
    }
};
