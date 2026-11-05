<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pesan asatid jadi dua arah:
 * - dari = 'admin'  : pesan admin untuk asatid (pesan lama otomatis 'admin')
 * - dari = 'asatid' : pesan asatid untuk admin (menu "Pesan untuk Admin" di dasbor asatid)
 * - balasan_untuk_id: pesan yang dibalas
 * Tabel ada di database UTAMA (koneksi "pusat"); migrasi ini ikut jalan di database sesi, jadi dicek dulu.
 */
return new class extends Migration
{
    public function up(): void
    {
        $skema = Schema::connection('pusat');
        if (! $skema->hasTable('pesan_asatid') || $skema->hasColumn('pesan_asatid', 'dari')) {
            return;
        }

        $skema->table('pesan_asatid', function (Blueprint $t) {
            $t->string('dari', 10)->default('admin')->after('pengirim_id');
            $t->foreignId('balasan_untuk_id')->nullable()->after('dari')->constrained('pesan_asatid')->nullOnDelete();
            $t->index(['dari', 'dibaca_at']);
        });
    }

    public function down(): void
    {
        if (DB::connection()->getDatabaseName() !== DB::connection('pusat')->getDatabaseName()) {
            return;
        }
        if (Schema::connection('pusat')->hasColumn('pesan_asatid', 'dari')) {
            Schema::connection('pusat')->table('pesan_asatid', function (Blueprint $t) {
                $t->dropConstrainedForeignId('balasan_untuk_id');
                $t->dropIndex(['dari', 'dibaca_at']);
                $t->dropColumn('dari');
            });
        }
    }
};
