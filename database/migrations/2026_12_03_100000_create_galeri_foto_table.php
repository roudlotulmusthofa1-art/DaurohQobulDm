<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Galeri Foto (Admin > Landing Page > Galeri Foto, halaman /galeri).
 * Database UTAMA (koneksi "pusat"), seperti berita.
 * - galeri_foto                 : foto unggahan admin (file di disk "bersama" folder galeri/)
 * - berita_foto.sembunyi_galeri : foto berita yang disembunyikan admin dari galeri
 *   (foto berita yang sudah ditampilkan otomatis ikut galeri, tidak disalin)
 */
return new class extends Migration
{
    public function up(): void
    {
        $pusat = Schema::connection('pusat');

        if (! $pusat->hasTable('galeri_foto')) {
            $pusat->create('galeri_foto', function (Blueprint $t) {
                $t->id();
                $t->string('path', 300);
                $t->string('keterangan', 200)->nullable();
                $t->date('tanggal')->nullable();          // tanggal kegiatan (kosong = tanggal unggah)
                $t->string('diunggah_oleh', 150)->nullable();
                $t->timestamps();
            });
        }

        if ($pusat->hasTable('berita_foto') && ! $pusat->hasColumn('berita_foto', 'sembunyi_galeri')) {
            $pusat->table('berita_foto', function (Blueprint $t) {
                $t->boolean('sembunyi_galeri')->default(false)->after('urutan');
            });
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDatabaseName() === DB::connection('pusat')->getDatabaseName()) {
            Schema::connection('pusat')->dropIfExists('galeri_foto');
            if (Schema::connection('pusat')->hasColumn('berita_foto', 'sembunyi_galeri')) {
                Schema::connection('pusat')->table('berita_foto', fn (Blueprint $t) => $t->dropColumn('sembunyi_galeri'));
            }
        }
    }
};
