<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kolom catatan_peserta: catatan dari peserta untuk admin (diisi di langkah terakhir formulir).
 * Aman dijalankan ulang: kalau kolomnya sudah ada, dilewati.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('pendaftaran_siswas', 'catatan_peserta')) {
            Schema::table('pendaftaran_siswas', function (Blueprint $t) {
                $t->text('catatan_peserta')->nullable()->after('rencana');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pendaftaran_siswas', 'catatan_peserta')) {
            Schema::table('pendaftaran_siswas', fn (Blueprint $t) => $t->dropColumn('catatan_peserta'));
        }
    }
};
