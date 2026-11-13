<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sistem > Daftar User: tombol Aktif / Nonaktif.
 * users.aktif = false -> akun tidak bisa masuk, dan yang sedang masuk langsung dikeluarkan (lihat CekSesiLogin).
 * Semua akun lama otomatis Aktif. Jalankan juga php artisan sesi:migrate (tabel users ada di tiap sesi).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'aktif')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('aktif')->default(true)->after('role_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'aktif')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('aktif');
            });
        }
    }
};
