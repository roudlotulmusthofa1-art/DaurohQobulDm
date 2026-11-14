<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ujian: cara pembuatan soal diatur admin di Pengaturan Ujian.
 * - ujian.cara_soal      : per_kitab (satu soal per kitab) | seluruh_fan (satu soal untuk semua kitab) | kelompok (diatur admin)
 * - ujian.kelompok_soal  : khusus "kelompok": [[2, 3], [5]] = kelompok 1 = kitab 2 + 3, kelompok 2 = kitab 5
 * - soal_ujian.kitab_ids : kitab yang dicakup satu soal (menggantikan kitab_id; isi lama disalin)
 * Data milik sesi (angkatan), jadi ikut dijalankan di database tiap sesi (php artisan sesi:migrate).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ujian') && ! Schema::hasColumn('ujian', 'cara_soal')) {
            Schema::table('ujian', function (Blueprint $table) {
                $table->string('cara_soal', 20)->default('per_kitab')->after('nilai_maks');
                $table->json('kelompok_soal')->nullable()->after('cara_soal');
            });
        }

        if (Schema::hasTable('soal_ujian') && ! Schema::hasColumn('soal_ujian', 'kitab_ids')) {
            Schema::table('soal_ujian', function (Blueprint $table) {
                $table->json('kitab_ids')->nullable()->after('ujian_id');
            });
            // Soal lama (satu kitab) -> kitab_ids = [kitab_id]
            DB::table('soal_ujian')->whereNotNull('kitab_id')->orderBy('id')->each(function ($s) {
                DB::table('soal_ujian')->where('id', $s->id)->update(['kitab_ids' => json_encode([(int) $s->kitab_id])]);
            });
            DB::table('soal_ujian')->whereNull('kitab_ids')->update(['kitab_ids' => '[]']);
            Schema::table('soal_ujian', function (Blueprint $table) {
                $table->dropConstrainedForeignId('kitab_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('soal_ujian', 'kitab_ids')) {
            Schema::table('soal_ujian', function (Blueprint $table) {
                $table->foreignId('kitab_id')->nullable()->after('ujian_id')->constrained('kitab')->nullOnDelete();
            });
            DB::table('soal_ujian')->orderBy('id')->each(function ($s) {
                $ids = json_decode((string) $s->kitab_ids, true) ?: [];
                DB::table('soal_ujian')->where('id', $s->id)->update(['kitab_id' => $ids[0] ?? null]);
            });
            Schema::table('soal_ujian', fn (Blueprint $table) => $table->dropColumn('kitab_ids'));
        }
        if (Schema::hasColumn('ujian', 'cara_soal')) {
            Schema::table('ujian', fn (Blueprint $table) => $table->dropColumn(['cara_soal', 'kelompok_soal']));
        }
    }
};
