<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel pendaftaran asatid (ustadz/guru).
 * Alurnya sama dengan pendaftaran siswa: daftar -> admin terima/tolak -> kalau diterima,
 * dibuatkan akun di tabel users dengan role "asatid".
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pendaftaran_asatid')) {
            return;
        }

        Schema::create('pendaftaran_asatid', function (Blueprint $t) {
            $t->id();
            $t->string('nomor_pendaftaran', 20)->unique();   // AST20260001

            // Data diri
            $t->string('nama_lengkap', 150);
            $t->string('nama_lengkap_arab', 150);
            $t->string('tempat_lahir', 100);
            $t->date('tanggal_lahir');
            $t->string('no_hp', 20);
            $t->string('alamat', 500);

            // Riwayat di Darul Musthofa
            $t->string('lama_belajar', 100);
            $t->string('tahun_selesai', 10);

            // Akun login (password sudah di-hash)
            $t->string('email')->unique();
            $t->string('password');

            $t->string('berkas_foto')->nullable();
            $t->text('catatan_peserta')->nullable();

            // Proses oleh admin
            $t->string('status', 20)->default('menunggu');   // menunggu | diterima | ditolak
            $t->text('catatan_admin')->nullable();
            $t->timestamp('diproses_at')->nullable();
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendaftaran_asatid');
    }
};
