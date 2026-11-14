<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Penilaian > Kelas & Pelajaran > Ujian Lisan (ujian dengan pertanyaan lisan).
 * - ujian_lisan       : pengaturan dari admin (tombol "Pengaturan Ujian Lisan"):
 *                       fan (bidang ilmu) + kitab (boleh kosong) + nilai maksimal tiap pertanyaan
 *                       (banyaknya isi "nilai_pertanyaan" = jumlah pertanyaan).
 * - nilai_ujian_lisan : nilai satu santri untuk satu ujian pada satu tanggal (dinilai lagi = diperbarui).
 *                       Nama fan, kitab & batas nilai disalin saat menilai, jadi kalau pengaturannya
 *                       diubah / dihapus nanti, nilai lama tetap terbaca.
 * Data milik sesi (angkatan), jadi ikut dijalankan di database tiap sesi (php artisan sesi:migrate).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ujian_lisan')) {
            Schema::create('ujian_lisan', function (Blueprint $table) {
                $table->id();
                $table->foreignId('bidang_ilmu_id')->nullable()->constrained('bidang_ilmu')->nullOnDelete();
                $table->foreignId('kitab_id')->nullable()->constrained('kitab')->nullOnDelete();
                $table->json('nilai_pertanyaan');                 // [20, 20, 20, 20, 20] = 5 pertanyaan
                $table->unsignedSmallInteger('total_maks')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('nilai_ujian_lisan')) {
            Schema::create('nilai_ujian_lisan', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pendaftaran_siswa_id')->constrained('pendaftaran_siswas')->cascadeOnDelete();
                $table->foreignId('ujian_lisan_id')->nullable()->constrained('ujian_lisan')->nullOnDelete();
                $table->string('fan', 150)->nullable();           // salinan nama fan saat menilai
                $table->string('kitab', 150)->nullable();         // salinan nama kitab saat menilai
                $table->date('tanggal');
                $table->json('maks');                             // salinan batas tiap pertanyaan
                $table->json('nilai');                            // nilai tiap pertanyaan
                $table->unsignedSmallInteger('total')->default(0);
                $table->unsignedSmallInteger('total_maks')->default(0);
                $table->foreignId('dinilai_oleh')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->unique(['pendaftaran_siswa_id', 'ujian_lisan_id', 'tanggal'], 'nilai_ujian_lisan_satu_per_hari');
                $table->index(['ujian_lisan_id', 'tanggal']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('nilai_ujian_lisan');
        Schema::dropIfExists('ujian_lisan');
    }
};
