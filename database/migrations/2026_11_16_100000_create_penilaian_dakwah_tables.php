<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Penilaian > Penilaian Dakwah: Khitoba' & Khurujud Dakwah.
 * - pilihan_dakwah : daftar yang ditulis admin sendiri lewat tombol "Pengaturan Nilai" / "Jenis Nilai"
 *                    (jenis nilai Khitoba', waktu Khitoba', jenis nilai Khuruj), lihat App\Models\PilihanDakwah::KELOMPOK
 * - nilai_dakwah   : hasil penilaian satu santri. Nama nilai & waktu disimpan sebagai tulisan,
 *                    jadi kalau pilihannya dihapus nanti, nilai lama tetap terbaca.
 * Data milik sesi (angkatan), jadi ikut dijalankan di database tiap sesi (php artisan sesi:migrate).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pilihan_dakwah')) {
            Schema::create('pilihan_dakwah', function (Blueprint $table) {
                $table->id();
                $table->string('kelompok', 20); // nilai_khitobah | waktu_khitobah | nilai_khuruj
                $table->string('nama', 100);
                $table->unsignedSmallInteger('urutan')->default(0);
                $table->timestamps();
                $table->index(['kelompok', 'urutan']);
            });
        }

        if (! Schema::hasTable('nilai_dakwah')) {
            Schema::create('nilai_dakwah', function (Blueprint $table) {
                $table->id();
                $table->string('jenis', 10); // khitobah | khuruj
                $table->foreignId('pendaftaran_siswa_id')->constrained('pendaftaran_siswas')->cascadeOnDelete();
                $table->string('waktu', 100)->nullable(); // waktu khitoba' (khusus khitobah)
                $table->date('tanggal');
                $table->string('nilai', 100);
                $table->text('catatan')->nullable();
                $table->foreignId('dinilai_oleh')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->index(['jenis', 'tanggal']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('nilai_dakwah');
        Schema::dropIfExists('pilihan_dakwah');
    }
};
