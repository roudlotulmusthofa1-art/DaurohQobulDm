<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fitur "Tambah Cepat" di Data Asatid & Data Admin:
 * admin cukup mengisi nama, WhatsApp, email, password. Data lain (nama Arab, alamat, dst.)
 * boleh kosong dulu dan dilengkapi belakangan lewat menu Edit.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pendaftaran_asatid')) {
            Schema::table('pendaftaran_asatid', function (Blueprint $t) {
                $t->string('nama_lengkap_arab', 150)->nullable()->change();
                $t->string('tempat_lahir', 100)->nullable()->change();
                $t->date('tanggal_lahir')->nullable()->change();
                $t->string('alamat', 500)->nullable()->change();
                $t->string('lama_belajar', 100)->nullable()->change();
                $t->string('tahun_selesai', 10)->nullable()->change();
            });
        }

        if (Schema::hasTable('pendaftaran_admin')) {
            Schema::table('pendaftaran_admin', function (Blueprint $t) {
                $t->string('nama_lengkap_arab', 150)->nullable()->change();
                $t->string('alamat', 500)->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        // Tidak dikembalikan menjadi wajib: data hasil tambah cepat bisa saja masih kosong.
    }
};
