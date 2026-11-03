<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Foto profil akun (Sistem > Pengaturan > Profil). Path file di disk "bersama" (storage/app/public),
 * karena akun admin & asatid dipakai bersama semua sesi.
 * Ikut dijalankan di database tiap sesi (php artisan sesi:migrate) supaya kolomnya ada di semua salinan akun.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'foto')) {
            Schema::table('users', fn (Blueprint $t) => $t->string('foto')->nullable()->after('role_id'));
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'foto')) {
            Schema::table('users', fn (Blueprint $t) => $t->dropColumn('foto'));
        }
    }
};
