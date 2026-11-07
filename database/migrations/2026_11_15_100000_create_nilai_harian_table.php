<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Penilaian > Kelas & Pelajaran > Nilai Harian.
 * Asatid menilai satu santri untuk satu kitab pada satu tanggal, dengan 6 penilaian
 * (batas nilai tiap penilaian ada di App\Models\NilaiHarian::ASPEK, totalnya 100).
 * Satu santri + satu kitab + satu tanggal = satu baris (dinilai lagi = diperbarui).
 * Data milik sesi (angkatan), jadi ikut dijalankan di database tiap sesi (php artisan sesi:migrate).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('nilai_harian')) {
            return;
        }
        Schema::create('nilai_harian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pendaftaran_siswa_id')->constrained('pendaftaran_siswas')->cascadeOnDelete();
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->nullOnDelete();
            $table->foreignId('kitab_id')->nullable()->constrained('kitab')->nullOnDelete();
            $table->date('tanggal');
            // 6 penilaian (batasnya: 20, 10, 20, 15, 20, 15)
            $table->unsignedTinyInteger('menjawab_pertanyaan')->default(0);
            $table->unsignedTinyInteger('kelengkapan_belajar')->default(0);
            $table->unsignedTinyInteger('konsentrasi_keaktifan')->default(0);
            $table->unsignedTinyInteger('menulis_faidah')->default(0);
            $table->unsignedTinyInteger('menjawab_sistematis')->default(0);
            $table->unsignedTinyInteger('tugas_tepat_waktu')->default(0);
            $table->unsignedTinyInteger('total')->default(0); // jumlah 6 penilaian, maksimal 100
            $table->foreignId('dinilai_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['pendaftaran_siswa_id', 'kitab_id', 'tanggal'], 'nilai_harian_satu_per_hari');
            $table->index(['kelas_id', 'kitab_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilai_harian');
    }
};
