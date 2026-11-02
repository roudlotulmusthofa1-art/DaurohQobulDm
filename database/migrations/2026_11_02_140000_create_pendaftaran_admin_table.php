<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel pendaftaran admin.
 * Alurnya sama dengan pendaftaran asatid: daftar lewat link khusus -> admin terima/tolak ->
 * kalau diterima, dibuatkan akun di tabel users dengan role "admin".
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pendaftaran_admin')) {
            return;
        }

        Schema::create('pendaftaran_admin', function (Blueprint $t) {
            $t->id();
            $t->string('nomor_pendaftaran', 20)->unique();   // ADM20260001

            // Data diri
            $t->string('nama_lengkap', 150);
            $t->string('nama_lengkap_arab', 150);
            $t->string('alamat', 500);
            $t->string('no_hp', 20);

            // Akun login (password sudah di-hash)
            $t->string('email')->unique();
            $t->string('password');

            $t->string('berkas_foto')->nullable();

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
        Schema::dropIfExists('pendaftaran_admin');
    }
};
