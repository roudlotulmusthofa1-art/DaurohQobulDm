<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Kurikulum & Kitab disederhanakan:
 * - hapus kolom pengarang dan jenis
 * - hapus tabel kitab_kelas (kelas dipilih di Jadwal Mengajar)
 * - tambah tahun_ajaran_id: "bulan ke" mengikuti jumlah bulan di tahun ajaran itu
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kitab', function (Blueprint $t) {
            $t->foreignId('tahun_ajaran_id')->nullable()->after('nama_arab')->constrained('tahun_ajaran')->nullOnDelete();
        });

        // Kitab yang sudah ada dimasukkan ke tahun ajaran aktif
        $tahunAktif = DB::table('tahun_ajaran')->where('is_active', true)->value('id');
        if ($tahunAktif) {
            DB::table('kitab')->whereNull('tahun_ajaran_id')->update(['tahun_ajaran_id' => $tahunAktif]);
        }

        Schema::table('kitab', function (Blueprint $t) {
            $t->dropColumn(['pengarang', 'jenis']);
        });

        Schema::dropIfExists('kitab_kelas');
    }

    public function down(): void
    {
        Schema::create('kitab_kelas', function (Blueprint $t) {
            $t->foreignId('kitab_id')->constrained('kitab')->cascadeOnDelete();
            $t->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $t->primary(['kitab_id', 'kelas_id']);
        });

        Schema::table('kitab', function (Blueprint $t) {
            $t->string('pengarang', 150)->nullable()->after('nama_arab');
            $t->string('jenis', 20)->default('teori')->after('bidang_ilmu_id');
            $t->dropConstrainedForeignId('tahun_ajaran_id');
        });
    }
};
