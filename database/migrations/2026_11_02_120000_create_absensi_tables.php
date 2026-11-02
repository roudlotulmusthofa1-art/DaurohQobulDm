<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Absensi peserta, diisi asatid dari dasbornya.
 *
 * - absensi        : satu sesi absen (satu kali guru mengabsen)
 *                    jenis "kelas"    -> kelas + pelajaran (bidang ilmu) + jam ke (waktu pelajaran)
 *                    jenis "kegiatan" -> jenis kegiatan + jam mulai, batas terlambat, batas alfa
 * - absensi_detail : status tiap peserta di sesi itu (hadir/terlambat/sakit/izin/alfa) + catatan mudarris
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            $table->string('jenis', 10); // kelas / kegiatan
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // asatid yang mengabsen
            $table->date('tanggal');
            $table->string('tanggal_hijriyah', 50)->nullable(); // mis. 1 Jumadil Awal 1448 H

            // Absensi kelas
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->nullOnDelete();
            $table->foreignId('bidang_ilmu_id')->nullable()->constrained('bidang_ilmu')->nullOnDelete();
            $table->foreignId('waktu_pelajaran_id')->nullable()->constrained('waktu_pelajaran')->nullOnDelete();

            // Absensi kegiatan
            $table->foreignId('jenis_kegiatan_id')->nullable()->constrained('jenis_kegiatan')->nullOnDelete();
            $table->time('jam_mulai')->nullable();
            $table->time('batas_terlambat')->nullable();
            $table->time('batas_alfa')->nullable();
            $table->string('metode', 10)->nullable(); // qr / manual
            $table->timestamp('selesai_at')->nullable(); // absensi kegiatan ditutup

            $table->timestamps();
            $table->index(['jenis', 'tanggal']);
        });

        Schema::create('absensi_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('absensi_id')->constrained('absensi')->cascadeOnDelete();
            $table->foreignId('pendaftaran_siswa_id')->constrained('pendaftaran_siswas')->cascadeOnDelete();
            $table->string('status', 10); // hadir / terlambat / sakit / izin / alfa
            $table->text('catatan')->nullable(); // catatan mudarris untuk peserta ini
            $table->time('waktu')->nullable();   // jam dicatat (absensi kegiatan)
            $table->timestamps();
            $table->unique(['absensi_id', 'pendaftaran_siswa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi_detail');
        Schema::dropIfExists('absensi');
    }
};
