<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Nilai Harian dihitung per BULAN AJARAN, bukan per tanggal:
 * satu santri + satu kitab + satu bulan ajaran = satu nilai, yang diteruskan asatid setiap hari.
 * - Tambah kolom bulan_ajaran_id, diisi dari tanggal nilai lama (masuk ke bulan ajaran yang periodenya cocok).
 * - Nilai lama yang dobel di bulan yang sama: yang paling baru dipakai, sisanya dihapus.
 * - Kunci unik diganti: (santri, kitab, tanggal) -> (santri, kitab, bulan ajaran).
 * Kolom "tanggal" tetap ada, artinya sekarang "terakhir dinilai".
 * Data milik sesi (angkatan): jalankan juga php artisan sesi:migrate.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('nilai_harian') || Schema::hasColumn('nilai_harian', 'bulan_ajaran_id')) {
            return;
        }

        Schema::table('nilai_harian', function (Blueprint $table) {
            $table->foreignId('bulan_ajaran_id')->nullable()->after('kitab_id')->constrained('bulan_ajaran')->nullOnDelete();
        });

        // Isi bulan ajaran dari tanggal nilai lama
        foreach (DB::table('bulan_ajaran')->whereNotNull('tanggal_mulai')->whereNotNull('tanggal_selesai')->get() as $b) {
            DB::table('nilai_harian')->whereNull('bulan_ajaran_id')
                ->whereBetween('tanggal', [$b->tanggal_mulai, $b->tanggal_selesai])
                ->update(['bulan_ajaran_id' => $b->id]);
        }

        // Nilai dobel di bulan yang sama: simpan yang paling baru saja
        $sudah = [];
        $hapus = [];
        $baris = DB::table('nilai_harian')->whereNotNull('bulan_ajaran_id')
            ->orderByDesc('tanggal')->orderByDesc('updated_at')->orderByDesc('id')
            ->get(['id', 'pendaftaran_siswa_id', 'kitab_id', 'bulan_ajaran_id']);
        foreach ($baris as $n) {
            $kunci = "{$n->pendaftaran_siswa_id}-{$n->kitab_id}-{$n->bulan_ajaran_id}";
            if (isset($sudah[$kunci])) {
                $hapus[] = $n->id;
            } else {
                $sudah[$kunci] = true;
            }
        }
        foreach (array_chunk($hapus, 500) as $potong) {
            DB::table('nilai_harian')->whereIn('id', $potong)->delete();
        }

        // Kunci unik baru dibuat dulu (indeks santri tetap ada untuk foreign key), baru yang lama dibuang
        Schema::table('nilai_harian', function (Blueprint $table) {
            $table->unique(['pendaftaran_siswa_id', 'kitab_id', 'bulan_ajaran_id'], 'nilai_harian_satu_per_bulan');
        });
        Schema::table('nilai_harian', function (Blueprint $table) {
            $table->dropUnique('nilai_harian_satu_per_hari');
            $table->index(['bulan_ajaran_id', 'kelas_id', 'kitab_id']);
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('nilai_harian', 'bulan_ajaran_id')) {
            return;
        }

        Schema::table('nilai_harian', function (Blueprint $table) {
            $table->unique(['pendaftaran_siswa_id', 'kitab_id', 'tanggal'], 'nilai_harian_satu_per_hari');
        });
        Schema::table('nilai_harian', function (Blueprint $table) {
            $table->dropUnique('nilai_harian_satu_per_bulan');
            $table->dropForeign(['bulan_ajaran_id']);
            $table->dropIndex(['bulan_ajaran_id', 'kelas_id', 'kitab_id']);
            $table->dropColumn('bulan_ajaran_id');
        });
    }
};
