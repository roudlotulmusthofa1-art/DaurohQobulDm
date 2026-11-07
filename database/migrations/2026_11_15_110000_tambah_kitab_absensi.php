<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Absensi kelas memilih KITAB (bukan lagi bidang ilmu), supaya nyambung dengan Penilaian > Nilai Harian.
 * absensi.bidang_ilmu_id tetap ada & tetap diisi dari bidang ilmu kitabnya, jadi absensi lama tetap tampil.
 * Data milik sesi (angkatan): jalankan juga php artisan sesi:migrate.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            if (! Schema::hasColumn('absensi', 'kitab_id')) {
                $table->foreignId('kitab_id')->nullable()->after('bidang_ilmu_id')->constrained('kitab')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            if (Schema::hasColumn('absensi', 'kitab_id')) {
                $table->dropConstrainedForeignId('kitab_id');
            }
        });
    }
};
