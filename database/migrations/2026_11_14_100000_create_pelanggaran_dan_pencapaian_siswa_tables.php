<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Penilaian > Pelanggaran & Pencapaian.
 * Asatid melaporkan pelanggaran / pencapaian satu siswa (jenis dari Master Data + tanggal + jam),
 * lalu semua laporan tampil di Admin > Penilaian.
 * Terlambat & Alfa dari absensi TIDAK disalin ke sini: dibaca langsung dari tabel absensi.
 * Data milik sesi (angkatan), jadi ikut dijalankan di database tiap sesi (php artisan sesi:migrate).
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['pelanggaran' => 'jenis_pelanggaran', 'pencapaian' => 'jenis_pencapaian'] as $nama => $tabelJenis) {
            if (Schema::hasTable("{$nama}_siswa")) {
                continue;
            }
            Schema::create("{$nama}_siswa", function (Blueprint $table) use ($tabelJenis) {
                $table->id();
                $table->foreignId('pendaftaran_siswa_id')->constrained('pendaftaran_siswas')->cascadeOnDelete();
                // Jenis dihapus dari Master Data -> laporan tetap ada, jenisnya kosong
                $table->foreignId("{$tabelJenis}_id")->nullable()->constrained($tabelJenis)->nullOnDelete();
                $table->date('tanggal');
                $table->time('jam')->nullable();
                $table->text('catatan')->nullable();
                $table->foreignId('dicatat_oleh')->nullable()->constrained('users')->nullOnDelete(); // asatid / admin pelapor
                $table->timestamps();
                $table->index('tanggal');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pencapaian_siswa');
        Schema::dropIfExists('pelanggaran_siswa');
    }
};
