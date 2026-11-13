<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Penilaian > Perizinan.
 * - jenis_izin  : nama jenis izin yang ditulis admin sendiri (tombol "Jenis Izin"), mis. Pulang, Berobat.
 * - izin_santri : satu izin untuk satu santri: jenis, mulai (tanggal + jam), berakhir (tanggal + jam), catatan,
 *                 dan waktu kembali (diisi lewat tombol "Sudah kembali").
 *                 Kembali lewat dari batas / belum kembali padahal batas lewat = TERLAMBAT
 *                 -> otomatis tampil di Penilaian > Catatan > Pelanggaran (App\Support\DataCatatan).
 *                 Nama jenis disimpan sebagai tulisan, jadi kalau jenisnya dihapus, izin lama tetap terbaca.
 *                 Semua waktu disimpan dalam WIB (Asia/Jakarta).
 * Data milik sesi (angkatan), jadi ikut dijalankan di database tiap sesi (php artisan sesi:migrate).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('jenis_izin')) {
            Schema::create('jenis_izin', function (Blueprint $table) {
                $table->id();
                $table->string('nama', 100)->unique();
                $table->unsignedSmallInteger('urutan')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('izin_santri')) {
            Schema::create('izin_santri', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pendaftaran_siswa_id')->constrained('pendaftaran_siswas')->cascadeOnDelete();
                $table->string('jenis', 100);
                $table->dateTime('mulai');
                $table->dateTime('selesai');
                $table->dateTime('kembali')->nullable(); // kosong = belum kembali
                $table->text('catatan')->nullable();
                $table->foreignId('diberi_oleh')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->index('mulai');
                $table->index(['kembali', 'selesai']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('izin_santri');
        Schema::dropIfExists('jenis_izin');
    }
};
