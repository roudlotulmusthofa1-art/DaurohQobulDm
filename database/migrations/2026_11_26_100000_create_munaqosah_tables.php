<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Akademik > Munaqosah (musyawarah asatid: lulus / diterima atau tidak).
 * - munaqosah_kolom : kolom isian yang ditulis admin sendiri untuk 3 bagian (ilmu | suluk | mandub)
 *                     jenis: teks (isian pendek) | teks_panjang | dropdown | centang; pilihan = json daftar tulisan.
 * - munaqosah       : satu baris per santri. isian = json {ilmu: [...], suluk: [...], mandub: [...]},
 *                     tiap butir {kolom_id, label, jenis, nilai} (label disalin, jadi kolom yang dihapus tidak merusak isian lama).
 *                     status: musyrifin (belum dikirim) | lajnah (sudah dikirim ke Lajnah Qobul Darul Musthofa).
 *                     keputusan: null (menunggu) | lulus | tidak.
 * Data milik sesi (angkatan), jadi ikut dijalankan di database tiap sesi (php artisan sesi:migrate).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('munaqosah_kolom')) {
            Schema::create('munaqosah_kolom', function (Blueprint $table) {
                $table->id();
                $table->string('bagian', 10);           // ilmu | suluk | mandub
                $table->string('label');
                $table->string('jenis', 15);            // teks | teks_panjang | dropdown | centang
                $table->json('pilihan')->nullable();    // untuk dropdown / centang
                $table->unsignedSmallInteger('urutan')->default(0);
                $table->timestamps();
                $table->index(['bagian', 'urutan']);
            });
        }

        if (! Schema::hasTable('munaqosah')) {
            Schema::create('munaqosah', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pendaftaran_siswa_id')->unique()->constrained('pendaftaran_siswas')->cascadeOnDelete();
                $table->json('isian')->nullable();
                $table->string('status', 10)->default('musyrifin'); // musyrifin | lajnah
                $table->foreignId('disimpan_oleh')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('dikirim_pada')->nullable();       // waktu dikirim ke Lajnah (WIB)
                $table->string('keputusan', 10)->nullable();          // null | lulus | tidak
                $table->text('catatan_lajnah')->nullable();
                $table->foreignId('diputuskan_oleh')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('diputuskan_pada')->nullable();
                $table->timestamps();
                $table->index('status');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('munaqosah');
        Schema::dropIfExists('munaqosah_kolom');
    }
};
