<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pita Pengumuman: tujuan tampilnya — landing (landing page), asatid (dasbor asatid), siswa (dasbor peserta).
 * Pengumuman yang sudah ada = landing. Tabel di database UTAMA (koneksi "pusat"); aman dijalankan berkali-kali.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::connection('pusat')->hasColumn('pita_pengumuman', 'tujuan')) {
            Schema::connection('pusat')->table('pita_pengumuman', function (Blueprint $t) {
                $t->string('tujuan', 10)->default('landing')->after('jenis');
                $t->index('tujuan');
            });
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDatabaseName() === DB::connection('pusat')->getDatabaseName()
            && Schema::connection('pusat')->hasColumn('pita_pengumuman', 'tujuan')) {
            Schema::connection('pusat')->table('pita_pengumuman', function (Blueprint $t) {
                $t->dropIndex(['tujuan']);
                $t->dropColumn('tujuan');
            });
        }
    }
};
