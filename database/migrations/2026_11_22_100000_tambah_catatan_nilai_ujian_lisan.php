<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ujian Lisan: catatan untuk tiap santri (diisi asatid / admin bersama nilainya, tampil di Admin > Ujian Lisan).
 * Data milik sesi (angkatan), jadi ikut dijalankan di database tiap sesi (php artisan sesi:migrate).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('nilai_ujian_lisan') && ! Schema::hasColumn('nilai_ujian_lisan', 'catatan')) {
            Schema::table('nilai_ujian_lisan', function (Blueprint $table) {
                $table->text('catatan')->nullable()->after('total_maks');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('nilai_ujian_lisan', 'catatan')) {
            Schema::table('nilai_ujian_lisan', fn (Blueprint $table) => $table->dropColumn('catatan'));
        }
    }
};
