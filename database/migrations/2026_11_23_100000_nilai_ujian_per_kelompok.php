<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Nilai Ujian mengikuti cara pembuatan soal dari admin (per kitab / seluruh fan / kelompok), sama seperti soal.
 * - nilai_ujian.kitab_ids : kitab yang dicakup nilai ini, mis. [2, 3]
 * - nilai_ujian.kelompok  : kunci kelompok = id kitab urut digabung "-", mis. "2-3"
 *   Satu santri + ujian + kelompok = satu nilai (menggantikan satu santri + ujian + kitab).
 * Nilai lama (satu kitab): kitab_ids = [kitab_id], kelompok = "kitab_id". Kolom kitab_id dihapus.
 * Data milik sesi (angkatan), jadi ikut dijalankan di database tiap sesi (php artisan sesi:migrate).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('nilai_ujian') || Schema::hasColumn('nilai_ujian', 'kelompok')) {
            return;
        }

        Schema::table('nilai_ujian', function (Blueprint $table) {
            $table->json('kitab_ids')->nullable()->after('ujian_id');
            $table->string('kelompok', 100)->default('')->after('kitab_ids');
        });

        DB::table('nilai_ujian')->orderBy('id')->each(function ($n) {
            DB::table('nilai_ujian')->where('id', $n->id)->update([
                'kitab_ids' => json_encode($n->kitab_id ? [(int) $n->kitab_id] : []),
                'kelompok'  => $n->kitab_id ? (string) $n->kitab_id : '',
            ]);
        });

        // Indeks baru dibuat dulu (dipakai foreign key santri & ujian), baru indeks lama dibuang
        Schema::table('nilai_ujian', function (Blueprint $table) {
            $table->unique(['pendaftaran_siswa_id', 'ujian_id', 'kelompok'], 'nilai_ujian_satu_per_kelompok');
            $table->index(['ujian_id', 'kelompok']);
        });
        Schema::table('nilai_ujian', function (Blueprint $table) {
            $table->dropForeign(['kitab_id']);
            $table->dropUnique('nilai_ujian_satu_per_kitab');
            $table->dropIndex(['ujian_id', 'kitab_id']);
            $table->dropColumn('kitab_id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('nilai_ujian', 'kelompok')) {
            return;
        }

        Schema::table('nilai_ujian', function (Blueprint $table) {
            $table->foreignId('kitab_id')->nullable()->after('ujian_id')->constrained('kitab')->nullOnDelete();
        });
        DB::table('nilai_ujian')->orderBy('id')->each(function ($n) {
            $ids = json_decode((string) $n->kitab_ids, true) ?: [];
            DB::table('nilai_ujian')->where('id', $n->id)->update(['kitab_id' => $ids[0] ?? null]);
        });
        Schema::table('nilai_ujian', function (Blueprint $table) {
            $table->unique(['pendaftaran_siswa_id', 'ujian_id', 'kitab_id'], 'nilai_ujian_satu_per_kitab');
            $table->index(['ujian_id', 'kitab_id']);
        });
        Schema::table('nilai_ujian', function (Blueprint $table) {
            $table->dropUnique('nilai_ujian_satu_per_kelompok');
            $table->dropIndex(['ujian_id', 'kelompok']);
            $table->dropColumn(['kitab_ids', 'kelompok']);
        });
    }
};
