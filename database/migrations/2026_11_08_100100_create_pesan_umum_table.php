<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pesan dari pengunjung landing page (tombol "Hubungi Admin").
 * Disimpan di database UTAMA (koneksi "pusat") supaya tidak tergantung sesi yang aktif.
 * Migrasi ini ikut dijalankan di database sesi, jadi tabel hanya dibuat bila belum ada.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('pusat')->hasTable('pesan_umum')) {
            return;
        }

        Schema::connection('pusat')->create('pesan_umum', function (Blueprint $t) {
            $t->id();
            $t->string('nama', 100);
            $t->string('kontak', 100);   // nomor WA atau email pengunjung
            $t->text('isi');
            $t->string('ip', 45)->nullable();
            $t->timestamp('dibaca_at')->nullable();
            $t->timestamps();

            $t->index('dibaca_at');
        });
    }

    public function down(): void
    {
        if (DB::connection()->getDatabaseName() === DB::connection('pusat')->getDatabaseName()) {
            Schema::connection('pusat')->dropIfExists('pesan_umum');
        }
    }
};
