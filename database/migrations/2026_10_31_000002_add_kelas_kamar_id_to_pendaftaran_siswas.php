<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menyambungkan pendaftar/santri ke Master Data Kelas & Kamar.
 *
 * - kelas_id -> kelas.id   (Master Data > Kelas)
 * - kamar_id -> kamar.id   (Master Data > Kamar)
 *
 * Kolom lama `kelas` dan `kamar` (angka 1-10) TIDAK diubah/dihapus,
 * supaya kalau dibatalkan (rollback) datanya masih ada.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pendaftaran_siswas', function (Blueprint $table) {
            // nullOnDelete: kalau kelas/kamar dihapus dari Master Data, isian santri jadi kosong (tidak error)
            $table->foreignId('kelas_id')->nullable()->after('kamar')->constrained('kelas')->nullOnDelete();
            $table->foreignId('kamar_id')->nullable()->after('kelas_id')->constrained('kamar')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pendaftaran_siswas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('kamar_id');
            $table->dropConstrainedForeignId('kelas_id');
        });
    }
};
