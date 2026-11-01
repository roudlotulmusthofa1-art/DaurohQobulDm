<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menyusulkan kolom yang tertinggal di database server.
 *
 * Kenapa perlu?
 * File migrasi lama (create_pendaftaran_siswas_table & create_users_table) sempat
 * ditambah kolom baru SETELAH migrasinya pernah dijalankan di server.
 * Laravel tidak menjalankan ulang migrasi yang sudah tercatat "Ran",
 * jadi kolom baru itu tidak pernah dibuat di server -> halaman error 500.
 *
 * Migrasi ini aman dijalankan di mana saja: kolom yang sudah ada dilewati.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Migrasi pendaftaran lama sudah dibuat "tambahkan kolom yang belum ada",
        // jadi cukup jalankan lagi isinya.
        $pendaftaran = require database_path('migrations/2026_10_20_000001_create_pendaftaran_siswas_table.php');
        $pendaftaran->up();

        // Kolom role di users (dulu dari migrasi add_role_to_users_table yang sudah dihapus)
        if (! Schema::hasColumn('users', 'role')) {
            Schema::table('users', fn (Blueprint $t) => $t->string('role', 20)->nullable()->after('password'));
        }
    }

    public function down(): void
    {
        // Sengaja kosong: kolom-kolom ini milik migrasi aslinya.
    }
};
