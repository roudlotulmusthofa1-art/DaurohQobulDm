<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pita Pengumuman: warna bebas pilihan admin (warna = 'sendiri' + kode warna, mis. #0f766e).
 * Tabel ada di database UTAMA (koneksi "pusat"); aman dijalankan berkali-kali (sesi:migrate).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::connection('pusat')->hasColumn('pita_pengumuman', 'warna_hex')) {
            Schema::connection('pusat')->table('pita_pengumuman', function (Blueprint $t) {
                $t->string('warna_hex', 7)->nullable()->after('warna');
            });
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDatabaseName() === DB::connection('pusat')->getDatabaseName()
            && Schema::connection('pusat')->hasColumn('pita_pengumuman', 'warna_hex')) {
            Schema::connection('pusat')->table('pita_pengumuman', fn (Blueprint $t) => $t->dropColumn('warna_hex'));
        }
    }
};
