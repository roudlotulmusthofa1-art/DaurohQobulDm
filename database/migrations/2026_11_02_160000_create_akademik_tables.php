<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menu Akademik:
 * - tahun_ajaran    : mis. 1448/1449, satu yang aktif
 * - bulan_ajaran    : pembagian tahun ajaran per bulan (pengganti semester), satu yang aktif
 * - kitab           : kurikulum (kitab per bidang ilmu, diajarkan di kelas mana, bulan ke berapa)
 * - kitab_kelas     : kitab <-> kelas
 * - jadwal_mengajar : jadwal per bulan ajaran: hari + jam + kelas + kitab + ustadz
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tahun_ajaran', function (Blueprint $t) {
            $t->id();
            $t->string('nama', 50);                 // 1448/1449
            $t->string('nama_masehi', 50)->nullable(); // 2026/2027
            $t->date('tanggal_mulai')->nullable();
            $t->date('tanggal_selesai')->nullable();
            $t->boolean('is_active')->default(false);
            $t->string('keterangan')->nullable();
            $t->timestamps();
        });

        Schema::create('bulan_ajaran', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->cascadeOnDelete();
            $t->string('nama', 50);                 // mis. Muharram 1448
            $t->unsignedTinyInteger('urutan')->default(1); // bulan ke-1, ke-2, ...
            $t->date('tanggal_mulai')->nullable();
            $t->date('tanggal_selesai')->nullable();
            $t->boolean('is_active')->default(false);
            $t->timestamps();
        });

        Schema::create('kitab', function (Blueprint $t) {
            $t->id();
            $t->string('nama', 150);
            $t->string('nama_arab', 150)->nullable();
            $t->string('pengarang', 150)->nullable();
            $t->foreignId('bidang_ilmu_id')->nullable()->constrained('bidang_ilmu')->nullOnDelete();
            $t->string('jenis', 20)->default('teori');  // teori / praktik
            $t->json('bulan_ke')->nullable();           // diajarkan di bulan ke berapa, mis. [1,2,3]
            $t->unsignedTinyInteger('frekuensi')->default(1); // pertemuan per minggu
            $t->text('deskripsi')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('kitab_kelas', function (Blueprint $t) {
            $t->foreignId('kitab_id')->constrained('kitab')->cascadeOnDelete();
            $t->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $t->primary(['kitab_id', 'kelas_id']);
        });

        Schema::create('jadwal_mengajar', function (Blueprint $t) {
            $t->id();
            $t->foreignId('bulan_ajaran_id')->constrained('bulan_ajaran')->cascadeOnDelete();
            $t->unsignedTinyInteger('hari');             // 1 = Senin ... 7 = Ahad
            $t->foreignId('waktu_pelajaran_id')->constrained('waktu_pelajaran')->cascadeOnDelete();
            $t->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $t->foreignId('kitab_id')->nullable()->constrained('kitab')->nullOnDelete();
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // ustadz (akun asatid)
            $t->string('catatan')->nullable();
            $t->timestamps();

            // Satu kelas hanya punya satu pelajaran di hari & jam yang sama
            $t->unique(['bulan_ajaran_id', 'hari', 'waktu_pelajaran_id', 'kelas_id'], 'jadwal_kelas_unik');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_mengajar');
        Schema::dropIfExists('kitab_kelas');
        Schema::dropIfExists('kitab');
        Schema::dropIfExists('bulan_ajaran');
        Schema::dropIfExists('tahun_ajaran');
    }
};
