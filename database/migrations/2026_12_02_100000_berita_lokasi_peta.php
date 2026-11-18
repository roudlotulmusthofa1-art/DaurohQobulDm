<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Berita:
 * - kolom lat & lng (titik peta lokasi, diisi dari tombol "Pakai lokasi saya" / "Cari tempat") di database UTAMA
 * - kategori berita "Alumni" dihapus dari Master Data (database sesi; ikut sesi:migrate di semua sesi).
 *   Berita lama berkategori Alumni tetap aman karena nama kategorinya disalin ke tabel berita.
 */
return new class extends Migration
{
    public function up(): void
    {
        $pusat = Schema::connection('pusat');
        if ($pusat->hasTable('berita') && ! $pusat->hasColumn('berita', 'lat')) {
            $pusat->table('berita', function (Blueprint $t) {
                $t->decimal('lat', 10, 7)->nullable()->after('lokasi');
                $t->decimal('lng', 10, 7)->nullable()->after('lat');
            });
        }

        if (Schema::hasTable('kategori_berita')) {
            DB::table('kategori_berita')->where('nama', 'Alumni')->delete();
        }
    }

    public function down(): void
    {
        $pusat = Schema::connection('pusat');
        if (DB::connection()->getDatabaseName() === DB::connection('pusat')->getDatabaseName()
            && $pusat->hasColumn('berita', 'lat')) {
            $pusat->table('berita', fn (Blueprint $t) => $t->dropColumn(['lat', 'lng']));
        }
    }
};
