<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Master Data "Jenis Pelanggaran" & "Jenis Pencapaian" dihapus (2026-11-06).
 * Penggantinya: Penilaian > Catatan (tabel catatan_siswa, dibuat di 2026_11_14_120000), jenisnya cukup Pencapaian / Pelanggaran.
 *
 * Laporan lama (pelanggaran_siswa / pencapaian_siswa, versi pertama menu Penilaian) DIPINDAHKAN dulu ke catatan_siswa
 * supaya tidak hilang. Isi catatannya = nama jenis lama + catatan lama, mis. "Membawa HP — disita pengurus".
 * Setelah itu tabel lama dibuang.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['pelanggaran' => 'jenis_pelanggaran', 'pencapaian' => 'jenis_pencapaian'] as $jenis => $tabelJenis) {
            $tabel = "{$jenis}_siswa";
            if (! Schema::hasTable($tabel) || ! Schema::hasTable('catatan_siswa')) {
                continue;
            }

            $q = DB::table($tabel)->orderBy("{$tabel}.id");
            if (Schema::hasTable($tabelJenis)) {
                $q->leftJoin($tabelJenis, "{$tabelJenis}.id", '=', "{$tabel}.{$tabelJenis}_id")
                    ->select("{$tabel}.*", "{$tabelJenis}.nama as nama_jenis");
            } else {
                $q->select("{$tabel}.*");
            }

            $q->chunk(500, function ($baris) use ($jenis) {
                DB::table('catatan_siswa')->insert($baris->map(fn ($b) => [
                    'pendaftaran_siswa_id' => $b->pendaftaran_siswa_id,
                    'jenis'                => $jenis,
                    'catatan'              => collect([$b->nama_jenis ?? null, $b->catatan])->filter()->implode(' — ') ?: ucfirst($jenis),
                    'tanggal'              => $b->tanggal,
                    'jam'                  => $b->jam,
                    'dicatat_oleh'         => $b->dicatat_oleh,
                    'created_at'           => $b->created_at,
                    'updated_at'           => $b->updated_at,
                ])->all());
            });
        }

        Schema::dropIfExists('pelanggaran_siswa');
        Schema::dropIfExists('pencapaian_siswa');
        Schema::dropIfExists('jenis_pelanggaran');
        Schema::dropIfExists('jenis_pencapaian');
    }

    public function down(): void
    {
        // Tidak dibuat kembali: fitur lamanya sudah dihapus.
    }
};
