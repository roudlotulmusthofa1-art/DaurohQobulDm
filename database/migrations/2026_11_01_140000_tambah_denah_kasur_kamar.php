<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Denah kamar:
 * - kamar.kasur_per_baris          : berapa kasur dalam satu baris di gambar denah
 * - pendaftaran_siswas.nomor_kasur : santri menempati kasur nomor berapa di kamarnya
 *
 * Satu kasur hanya untuk satu santri (unique kamar_id + nomor_kasur).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('kamar', 'kasur_per_baris')) {
            Schema::table('kamar', fn (Blueprint $t) => $t->unsignedTinyInteger('kasur_per_baris')->default(4));
        }

        if (! Schema::hasColumn('pendaftaran_siswas', 'nomor_kasur')) {
            Schema::table('pendaftaran_siswas', function (Blueprint $t) {
                $t->unsignedSmallInteger('nomor_kasur')->nullable();
                $t->unique(['kamar_id', 'nomor_kasur'], 'kasur_unik_per_kamar');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pendaftaran_siswas', 'nomor_kasur')) {
            Schema::table('pendaftaran_siswas', function (Blueprint $t) {
                $t->dropUnique('kasur_unik_per_kamar');
                $t->dropColumn('nomor_kasur');
            });
        }

        if (Schema::hasColumn('kamar', 'kasur_per_baris')) {
            Schema::table('kamar', fn (Blueprint $t) => $t->dropColumn('kasur_per_baris'));
        }
    }
};
