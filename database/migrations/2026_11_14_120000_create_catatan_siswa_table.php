<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Penilaian > Catatan: asatid mencatat PENCAPAIAN atau PELANGGARAN satu santri
 * (isi catatan + tanggal + jam), lalu semua catatan tampil di Admin > Penilaian > Catatan.
 * Terlambat & Alfa dari absensi TIDAK disalin ke sini: dibaca langsung dari tabel absensi.
 * Data milik sesi (angkatan), jadi ikut dijalankan di database tiap sesi (php artisan sesi:migrate).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('catatan_siswa')) {
            return;
        }
        Schema::create('catatan_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pendaftaran_siswa_id')->constrained('pendaftaran_siswas')->cascadeOnDelete();
            $table->string('jenis', 15); // pencapaian | pelanggaran
            $table->text('catatan');
            $table->date('tanggal');
            $table->time('jam')->nullable();
            $table->foreignId('dicatat_oleh')->nullable()->constrained('users')->nullOnDelete(); // asatid / admin pelapor
            $table->timestamps();
            $table->index(['jenis', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catatan_siswa');
    }
};
