<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pita Pengumuman: kabar singkat di paling atas landing page
 * (mis. "Pendaftaran Gelombang 1 dibuka 10–20 Nov", "Sedang masa dauroh ...") + hitung mundur.
 * Diatur di Admin > Landing Page > Pita Pengumuman.
 * Disimpan di database UTAMA (koneksi "pusat") karena landing page satu untuk semua sesi.
 * Migrasi ini ikut dijalankan di database sesi (sesi:migrate), jadi tabel hanya dibuat bila belum ada.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('pusat')->hasTable('pita_pengumuman')) {
            return;
        }

        Schema::connection('pusat')->create('pita_pengumuman', function (Blueprint $t) {
            $t->id();
            $t->string('jenis', 20)->default('info');      // pendaftaran | dauroh | info
            $t->string('judul', 150);
            $t->string('keterangan', 255)->nullable();
            $t->dateTime('mulai_at');                       // disimpan dalam UTC
            $t->dateTime('selesai_at');
            $t->dateTime('tampil_sejak')->nullable();       // boleh tampil lebih awal: "dimulai dalam ..."
            $t->boolean('hitung_mundur')->default(true);
            $t->string('tombol_teks', 40)->nullable();
            $t->string('tombol_url', 255)->nullable();
            $t->string('warna', 20)->default('hijau');
            $t->unsignedInteger('urutan')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        if (DB::connection()->getDatabaseName() === DB::connection('pusat')->getDatabaseName()) {
            Schema::connection('pusat')->dropIfExists('pita_pengumuman');
        }
    }
};
