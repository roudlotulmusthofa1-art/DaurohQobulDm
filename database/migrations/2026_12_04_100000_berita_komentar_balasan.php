<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Balasan komentar berita (database UTAMA):
 * kolom induk_id = komentar yang dibalas (kosong = komentar biasa).
 * Hanya admin & penulis berita yang boleh membalas (diperiksa di BeritaController).
 * Komentar induk dihapus -> balasannya ikut terhapus.
 */
return new class extends Migration
{
    public function up(): void
    {
        $pusat = Schema::connection('pusat');
        if ($pusat->hasTable('berita_komentar') && ! $pusat->hasColumn('berita_komentar', 'induk_id')) {
            $pusat->table('berita_komentar', function (Blueprint $t) {
                $t->foreignId('induk_id')->nullable()->after('berita_id')
                    ->constrained('berita_komentar')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        $pusat = Schema::connection('pusat');
        if (DB::connection()->getDatabaseName() === DB::connection('pusat')->getDatabaseName()
            && $pusat->hasColumn('berita_komentar', 'induk_id')) {
            $pusat->table('berita_komentar', function (Blueprint $t) {
                $t->dropConstrainedForeignId('induk_id');
            });
        }
    }
};
