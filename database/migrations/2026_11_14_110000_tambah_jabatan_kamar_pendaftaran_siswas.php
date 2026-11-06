<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Data Santri > kartu kamar: penanda Ketua & Wakil kamar.
 * pendaftaran_siswas.jabatan_kamar = 'ketua' | 'wakil' | null (anggota biasa).
 * Satu kamar paling banyak 1 ketua & 1 wakil (dijaga di AdminSantriController@jabatanKamar).
 * Pindah kamar -> jabatan dikosongkan (PendaftaranSiswa::booted).
 * Data milik sesi (angkatan), jadi ikut dijalankan di database tiap sesi (php artisan sesi:migrate).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('pendaftaran_siswas', 'jabatan_kamar')) {
            Schema::table('pendaftaran_siswas', function (Blueprint $t) {
                $t->string('jabatan_kamar', 10)->nullable()->after('nomor_kasur');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pendaftaran_siswas', 'jabatan_kamar')) {
            Schema::table('pendaftaran_siswas', function (Blueprint $t) {
                $t->dropColumn('jabatan_kamar');
            });
        }
    }
};
