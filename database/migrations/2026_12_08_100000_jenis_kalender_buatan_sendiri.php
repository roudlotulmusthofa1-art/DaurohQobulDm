<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kalender Pendidikan: jenis kegiatan dibuat admin sendiri (nama + warna), menggantikan 6 jenis bawaan.
 * - jenis_kalender                   : nama, warna (kunci JenisKalender::WARNA), urutan
 * - kalender_pendidikan.jenis_id     : jenis kegiatan (kosong = "Tanpa jenis"; jenis dihapus = kosong)
 * - kolom lama jenis & jenis_lain dihapus (belum pernah dipakai di server).
 * Per sesi (php artisan sesi:migrate).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('jenis_kalender')) {
            Schema::create('jenis_kalender', function (Blueprint $table) {
                $table->id();
                $table->string('nama', 60);
                $table->string('warna', 20)->default('emerald');
                $table->unsignedSmallInteger('urutan')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('kalender_pendidikan')) {
            return;
        }

        if (! Schema::hasColumn('kalender_pendidikan', 'jenis_id')) {
            Schema::table('kalender_pendidikan', function (Blueprint $table) {
                $table->foreignId('jenis_id')->nullable()->after('judul')->constrained('jenis_kalender')->nullOnDelete();
            });
        }

        foreach (['jenis', 'jenis_lain'] as $kolom) {
            if (Schema::hasColumn('kalender_pendidikan', $kolom)) {
                Schema::table('kalender_pendidikan', fn (Blueprint $table) => $table->dropColumn($kolom));
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('kalender_pendidikan') && Schema::hasColumn('kalender_pendidikan', 'jenis_id')) {
            Schema::table('kalender_pendidikan', function (Blueprint $table) {
                $table->dropConstrainedForeignId('jenis_id');
                $table->string('jenis', 30)->default('kegiatan');
            });
        }
        Schema::dropIfExists('jenis_kalender');
    }
};
