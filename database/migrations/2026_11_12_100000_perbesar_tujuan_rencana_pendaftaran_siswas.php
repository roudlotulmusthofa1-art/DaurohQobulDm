<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kolom "Tujuan mondok" (tujuan) dan "Rencana setelah mondok" (rencana) diperbesar dari VARCHAR(255) ke TEXT.
 * Sebelumnya formulir mengizinkan 500/2000 huruf, tetapi kolomnya hanya muat 255 huruf,
 * sehingga jawaban yang panjang membuat formulir pendaftaran & edit santri gagal disimpan (error 500).
 * Tabel ini ada di database tiap sesi, jadi migrasi ini juga dijalankan oleh "php artisan sesi:migrate".
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pendaftaran_siswas')) {
            return;
        }

        Schema::table('pendaftaran_siswas', function (Blueprint $t) {
            if (Schema::hasColumn('pendaftaran_siswas', 'tujuan')) {
                $t->text('tujuan')->nullable()->change();
            }
            if (Schema::hasColumn('pendaftaran_siswas', 'rencana')) {
                $t->text('rencana')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        // Sengaja tidak dikecilkan lagi: tulisan yang sudah panjang akan terpotong / gagal.
    }
};
