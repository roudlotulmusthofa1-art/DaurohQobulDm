<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menyusulkan kolom riwayat_pondok & riwayat_kitab (dipakai form pendaftaran PSB).
 *
 * Kedua kolom ini di migrasi lama hanya ada di bagian "buat tabel baru",
 * yang tidak dijalankan di server karena tabelnya sudah ada.
 * Kolom yang sudah ada dilewati, jadi aman dijalankan di mana saja.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['riwayat_pondok', 'riwayat_kitab'] as $kolom) {
            if (! Schema::hasColumn('pendaftaran_siswas', $kolom)) {
                Schema::table('pendaftaran_siswas', fn (Blueprint $t) => $t->json($kolom)->nullable());
            }
        }
    }

    public function down(): void
    {
        // Sengaja kosong: kolom ini milik tabel pendaftaran aslinya.
    }
};
