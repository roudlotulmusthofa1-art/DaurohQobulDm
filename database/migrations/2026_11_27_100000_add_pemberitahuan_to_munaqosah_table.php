<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Laporan > Laporan Kelulusan: tanda santri sudah diberitahu hasil Istimaroh lewat WhatsApp.
 * - diberitahu_pada      : waktu terakhir diberitahu (WIB)
 * - diberitahu_lewat     : fonnte (otomatis) | wa (WhatsApp admin sendiri)
 * - diberitahu_keputusan : keputusan saat diberitahu; bila keputusan diubah sesudahnya, tanda dianggap "belum"
 * Data milik sesi, jadi ikut dijalankan di database tiap sesi (php artisan sesi:migrate).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('munaqosah') || Schema::hasColumn('munaqosah', 'diberitahu_pada')) {
            return;
        }
        Schema::table('munaqosah', function (Blueprint $table) {
            $table->timestamp('diberitahu_pada')->nullable()->after('diputuskan_pada');
            $table->string('diberitahu_lewat', 10)->nullable()->after('diberitahu_pada');
            $table->string('diberitahu_keputusan', 10)->nullable()->after('diberitahu_lewat');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('munaqosah', 'diberitahu_pada')) {
            return;
        }
        Schema::table('munaqosah', function (Blueprint $table) {
            $table->dropColumn(['diberitahu_pada', 'diberitahu_lewat', 'diberitahu_keputusan']);
        });
    }
};
