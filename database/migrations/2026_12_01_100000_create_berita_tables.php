<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Berita (Admin > Landing Page > Berita, menu Tulis Berita asatid & siswa, bagian Berita Terkini di landing page).
 * Disimpan di database UTAMA (koneksi "pusat") supaya tidak hilang saat sesi diganti.
 * Migrasi ini ikut dijalankan di database sesi, jadi tabel hanya dibuat bila belum ada.
 *
 * - berita           : tulisan + status (menunggu / tampil / ditolak) + batas waktu tampil
 * - berita_foto      : foto berita (boleh banyak), file di disk "bersama" folder berita/
 * - berita_suka      : tanda suka, satu per browser (kolom kunci = kode acak dari cookie)
 * - berita_komentar  : komentar pengunjung
 */
return new class extends Migration
{
    public function up(): void
    {
        $pusat = Schema::connection('pusat');

        if (! $pusat->hasTable('berita')) {
            $pusat->create('berita', function (Blueprint $t) {
                $t->id();
                $t->string('slug', 220)->unique();
                $t->string('judul', 200);
                $t->string('ringkasan', 300)->nullable();
                $t->longText('isi');
                $t->string('kategori', 80)->nullable();        // nama kategori disalin dari Master Data
                $t->string('kategori_warna', 20)->nullable();
                $t->string('lokasi', 80)->nullable();          // contoh: SUMATERA SELATAN
                // Penulis (disalin, supaya tetap tampil walau akunnya pindah sesi / dihapus)
                $t->unsignedBigInteger('penulis_user_id')->nullable();
                $t->string('penulis_peran', 20);               // admin | asatid | siswa
                $t->string('penulis_nama', 150);
                $t->string('penulis_foto', 500)->nullable();
                $t->unsignedBigInteger('sesi_id')->nullable();
                // Pemeriksaan admin
                $t->string('status', 20)->default('menunggu'); // menunggu | tampil | ditolak
                $t->timestamp('tampil_mulai')->nullable();
                $t->timestamp('tampil_sampai')->nullable();    // kosong = tampil terus
                $t->text('catatan_admin')->nullable();
                $t->string('diperiksa_oleh', 150)->nullable();
                $t->timestamp('diperiksa_at')->nullable();
                $t->unsignedInteger('jumlah_dilihat')->default(0);
                $t->unsignedInteger('jumlah_suka')->default(0);
                $t->timestamps();

                $t->index(['status', 'tampil_sampai']);
                $t->index(['penulis_peran', 'penulis_user_id']);
            });
        }

        if (! $pusat->hasTable('berita_foto')) {
            $pusat->create('berita_foto', function (Blueprint $t) {
                $t->id();
                $t->foreignId('berita_id')->constrained('berita')->cascadeOnDelete();
                $t->string('path', 300);
                $t->unsignedSmallInteger('urutan')->default(0);
                $t->timestamps();
            });
        }

        if (! $pusat->hasTable('berita_suka')) {
            $pusat->create('berita_suka', function (Blueprint $t) {
                $t->id();
                $t->foreignId('berita_id')->constrained('berita')->cascadeOnDelete();
                $t->string('kunci', 64);
                $t->timestamps();
                $t->unique(['berita_id', 'kunci']);
            });
        }

        if (! $pusat->hasTable('berita_komentar')) {
            $pusat->create('berita_komentar', function (Blueprint $t) {
                $t->id();
                $t->foreignId('berita_id')->constrained('berita')->cascadeOnDelete();
                $t->string('nama', 100);
                $t->text('isi');
                $t->unsignedBigInteger('user_id')->nullable();
                $t->string('peran', 20)->nullable();           // diisi bila penulis komentar sedang login
                $t->string('kunci', 64)->nullable();           // browser penulis (boleh menghapus komentarnya sendiri)
                $t->string('ip', 45)->nullable();
                $t->timestamps();
            });
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDatabaseName() === DB::connection('pusat')->getDatabaseName()) {
            Schema::connection('pusat')->dropIfExists('berita_komentar');
            Schema::connection('pusat')->dropIfExists('berita_suka');
            Schema::connection('pusat')->dropIfExists('berita_foto');
            Schema::connection('pusat')->dropIfExists('berita');
        }
    }
};
