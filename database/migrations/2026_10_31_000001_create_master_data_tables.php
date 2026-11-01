<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Bidang Ilmu (fann) untuk kurikulum kitab
        Schema::create('bidang_ilmu', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('slug', 120)->unique();
            $table->string('warna', 30)->nullable();      // contoh: bg-blue-100
            $table->text('deskripsi')->nullable();
            $table->unsignedInteger('urutan')->default(0)->index();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Waktu Pelajaran
        Schema::create('waktu_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);                   // contoh: Ba'da Subuh
            $table->time('jam_mulai')->nullable();
            $table->time('jam_selesai')->nullable();
            $table->string('keterangan', 255)->nullable();
            $table->unsignedInteger('urutan')->default(0)->index();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Kelas
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('kode', 30)->nullable()->unique();
            $table->unsignedTinyInteger('tingkat')->nullable();
            $table->text('deskripsi')->nullable();
            $table->unsignedInteger('urutan')->default(0)->index();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 4. Kamar (asrama)
        Schema::create('kamar', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('kode', 30)->nullable()->unique();
            $table->string('gedung', 100)->nullable();
            $table->enum('jenis', ['putra', 'putri'])->default('putra');
            $table->unsignedSmallInteger('kapasitas')->default(0);
            $table->text('deskripsi')->nullable();
            $table->unsignedInteger('urutan')->default(0)->index();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 5. Kategori Berita
        Schema::create('kategori_berita', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('slug', 120)->unique();
            $table->string('warna', 30)->nullable();
            $table->text('deskripsi')->nullable();
            $table->unsignedInteger('urutan')->default(0)->index();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kategori_berita');
        Schema::dropIfExists('kamar');
        Schema::dropIfExists('kelas');
        Schema::dropIfExists('waktu_pelajaran');
        Schema::dropIfExists('bidang_ilmu');
    }
};
