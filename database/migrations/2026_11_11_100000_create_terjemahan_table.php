<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sistem > Pengaturan > Bahasa
 * Tabel "terjemahan": terjemahan Arab yang diisi admin sendiri untuk setiap tulisan tampilan __('...').
 * Disimpan di database UTAMA (koneksi "pusat"), jadi berlaku untuk semua sesi.
 * - teks_id : tulisan bahasa Indonesia persis seperti di __('...')
 * - kunci   : md5(teks_id), supaya tulisan panjang tetap bisa dibuat unik
 * - teks_ar : terjemahan Arab dari admin
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::connection('pusat')->hasTable('terjemahan')) {
            Schema::connection('pusat')->create('terjemahan', function (Blueprint $t) {
                $t->id();
                $t->char('kunci', 32)->unique();
                $t->text('teks_id');
                $t->text('teks_ar');
                $t->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::connection('pusat')->dropIfExists('terjemahan');
    }
};
