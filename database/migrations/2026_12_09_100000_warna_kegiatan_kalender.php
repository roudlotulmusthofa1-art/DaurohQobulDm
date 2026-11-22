<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Kalender Pendidikan: warna dipilih langsung per kegiatan (kode warna, mis. "#10b981"), tanpa Jenis.
 * - kalender_pendidikan.warna : kode warna hex 7 huruf
 * - kegiatan lama mengambil warna jenisnya (jenis_kalender.warna) supaya tampilannya tidak berubah.
 * Tabel jenis_kalender & kolom jenis_id dibiarkan (tidak dipakai lagi) supaya tidak ada data yang hilang.
 * Per sesi (php artisan sesi:migrate).
 */
return new class extends Migration
{
    /** Warna lama (nama Tailwind) => kode hex (warna -500) */
    private const HEX_LAMA = [
        'emerald' => '#10b981', 'teal' => '#14b8a6', 'sky' => '#0ea5e9', 'blue' => '#3b82f6',
        'indigo' => '#6366f1', 'violet' => '#8b5cf6', 'pink' => '#ec4899', 'rose' => '#f43f5e',
        'orange' => '#f97316', 'amber' => '#f59e0b', 'lime' => '#84cc16', 'slate' => '#64748b',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('kalender_pendidikan') || Schema::hasColumn('kalender_pendidikan', 'warna')) {
            return;
        }

        Schema::table('kalender_pendidikan', function (Blueprint $table) {
            $table->string('warna', 7)->default('#10b981')->after('judul');
        });

        if (Schema::hasColumn('kalender_pendidikan', 'jenis_id') && Schema::hasTable('jenis_kalender')) {
            $warnaJenis = DB::table('jenis_kalender')->pluck('warna', 'id');
            DB::table('kalender_pendidikan')->select('id', 'jenis_id')->orderBy('id')->get()->each(function ($k) use ($warnaJenis) {
                $nama = $k->jenis_id ? ($warnaJenis[$k->jenis_id] ?? 'slate') : 'slate';
                DB::table('kalender_pendidikan')->where('id', $k->id)->update(['warna' => self::HEX_LAMA[$nama] ?? '#64748b']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('kalender_pendidikan') && Schema::hasColumn('kalender_pendidikan', 'warna')) {
            Schema::table('kalender_pendidikan', fn (Blueprint $table) => $table->dropColumn('warna'));
        }
    }
};
