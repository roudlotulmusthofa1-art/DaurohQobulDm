<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pesan dari admin untuk asatid (Data Asatid > Kirim Pesan, dibaca di dasbor asatid > Pesan dari Admin).
 * Disimpan di database UTAMA (koneksi "pusat") karena akun asatid dipakai bersama semua sesi.
 * Pesan ke banyak asatid disalin satu baris per asatid (supaya status "dibaca" tercatat per orang).
 * Migrasi ini ikut dijalankan di database sesi (php artisan sesi:migrate), jadi tabel hanya dibuat bila belum ada.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('pusat')->hasTable('pesan_asatid')) {
            return;
        }

        Schema::connection('pusat')->create('pesan_asatid', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();      // akun asatid penerima
            $t->foreignId('pengirim_id')->nullable()->constrained('users')->nullOnDelete(); // admin pengirim
            $t->string('judul', 150)->nullable();
            $t->text('isi');
            $t->timestamp('dibaca_at')->nullable();
            $t->timestamps();

            $t->index(['user_id', 'dibaca_at']);
        });
    }

    public function down(): void
    {
        // Hanya dihapus dari database utama
        if (DB::connection()->getDatabaseName() === DB::connection('pusat')->getDatabaseName()) {
            Schema::connection('pusat')->dropIfExists('pesan_asatid');
        }
    }
};
