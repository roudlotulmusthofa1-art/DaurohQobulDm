<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Form pendaftaran: "Apakah Anda pernah mengikuti Dauroh Qobul sebelumnya?"
 * Nilai: 'ya' (نعم) / 'tidak' (لا). Pendaftar lama = kosong.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('pendaftaran_siswas', 'pernah_dauroh')) {
            Schema::table('pendaftaran_siswas', function (Blueprint $t) {
                $t->string('pernah_dauroh', 10)->nullable()->after('rencana');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pendaftaran_siswas', 'pernah_dauroh')) {
            Schema::table('pendaftaran_siswas', fn (Blueprint $t) => $t->dropColumn('pernah_dauroh'));
        }
    }
};
