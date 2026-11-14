<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Penilaian > Kelas & Pelajaran > Ujian (ujian tulis).
 * - ujian        : pengaturan dari admin: nama / periode (bebas ditulis), fan, nilai maksimal, aktif.
 * - ujian_kitab  : kitab yang diujikan di ujian itu (satu fan boleh beberapa kitab).
 * - soal_ujian   : soal buatan asatid untuk satu kitab + halaqoh yang dipilih. Butir = [{pertanyaan, jawaban, nilai}].
 *                  "butir_asli" = kiriman asatid (untuk menandai butir yang diubah admin).
 *                  Status: menunggu -> disetujui (admin mengedit lalu menyetujui = dikirim ke asatid).
 * - nilai_ujian  : satu santri + ujian + kitab = satu nilai (dinilai lagi = diperbarui).
 * Nama ujian, fan & kitab disalin ke soal dan nilai, jadi pengaturan yang dihapus tidak merusak data lama.
 * Data milik sesi (angkatan), jadi ikut dijalankan di database tiap sesi (php artisan sesi:migrate).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ujian')) {
            Schema::create('ujian', function (Blueprint $table) {
                $table->id();
                $table->string('nama', 150)->nullable();          // nama / periode, mis. "Ujian Akhir Muharram"
                $table->foreignId('bidang_ilmu_id')->nullable()->constrained('bidang_ilmu')->nullOnDelete();
                $table->unsignedSmallInteger('nilai_maks')->default(100);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ujian_kitab')) {
            Schema::create('ujian_kitab', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ujian_id')->constrained('ujian')->cascadeOnDelete();
                $table->foreignId('kitab_id')->constrained('kitab')->cascadeOnDelete();
                $table->unique(['ujian_id', 'kitab_id']);
            });
        }

        if (! Schema::hasTable('soal_ujian')) {
            Schema::create('soal_ujian', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ujian_id')->nullable()->constrained('ujian')->nullOnDelete();
                $table->foreignId('kitab_id')->nullable()->constrained('kitab')->nullOnDelete();
                $table->string('nama_ujian', 150)->nullable();    // salinan
                $table->string('fan', 150)->nullable();           // salinan
                $table->string('kitab', 150)->nullable();         // salinan
                $table->json('kelas_ids');                        // halaqoh yang diujikan
                $table->json('butir');                            // [{pertanyaan, jawaban, nilai}]
                $table->json('butir_asli')->nullable();           // kiriman asatid sebelum diperiksa
                $table->string('status', 20)->default('menunggu'); // menunggu | disetujui
                $table->text('catatan_admin')->nullable();
                $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('diperiksa_oleh')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('diperiksa_at')->nullable();
                $table->timestamps();
                $table->index(['status', 'ujian_id']);
            });
        }

        if (! Schema::hasTable('nilai_ujian')) {
            Schema::create('nilai_ujian', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pendaftaran_siswa_id')->constrained('pendaftaran_siswas')->cascadeOnDelete();
                $table->foreignId('ujian_id')->nullable()->constrained('ujian')->nullOnDelete();
                $table->foreignId('kitab_id')->nullable()->constrained('kitab')->nullOnDelete();
                $table->foreignId('kelas_id')->nullable()->constrained('kelas')->nullOnDelete();
                $table->string('nama_ujian', 150)->nullable();    // salinan
                $table->string('fan', 150)->nullable();           // salinan
                $table->string('kitab', 150)->nullable();         // salinan
                $table->unsignedSmallInteger('nilai')->default(0);
                $table->unsignedSmallInteger('nilai_maks')->default(100);
                $table->date('tanggal');
                $table->foreignId('dinilai_oleh')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->unique(['pendaftaran_siswa_id', 'ujian_id', 'kitab_id'], 'nilai_ujian_satu_per_kitab');
                $table->index(['ujian_id', 'kitab_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('nilai_ujian');
        Schema::dropIfExists('soal_ujian');
        Schema::dropIfExists('ujian_kitab');
        Schema::dropIfExists('ujian');
    }
};
