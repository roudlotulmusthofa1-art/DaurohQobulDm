<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dasbor admin > Pergantian Pelajaran: admin mengirim "tanda ganti pelajaran" ke guru
 * untuk satu jadwal mengajar pada satu tanggal. Guru melihatnya sebagai jendela pengingat di web asatid.
 * Satu jadwal + satu tanggal = satu baris (dikirim ulang = waktu kirim diperbarui, status dilihat dikosongkan).
 * Data milik sesi (angkatan): jalankan juga php artisan sesi:migrate.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tanda_pelajaran')) {
            return;
        }
        Schema::create('tanda_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_mengajar_id')->constrained('jadwal_mengajar')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // guru yang diberi tanda
            $table->date('tanggal');
            $table->foreignId('dikirim_oleh')->nullable()->constrained('users')->nullOnDelete(); // admin
            $table->timestamp('dikirim_at')->nullable();
            $table->timestamp('dilihat_at')->nullable();
            $table->timestamps();
            $table->unique(['jadwal_mengajar_id', 'tanggal']);
            $table->index(['user_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tanda_pelajaran');
    }
};
