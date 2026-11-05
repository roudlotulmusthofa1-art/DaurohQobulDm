<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Absensi kegiatan dibuat & diaktifkan admin (Admin > Akademik > Absensi Kegiatan),
 * lalu dicatat BERSAMA oleh semua asatid.
 *
 * - absensi.dari_admin          : true = dibuat admin (semua asatid boleh mencatat)
 * - absensi.aktif               : true = tampil di dasbor asatid (bisa diaktifkan / dinonaktifkan admin)
 * - absensi_detail.dicatat_oleh : akun asatid/admin yang mencatat peserta ini
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            if (! Schema::hasColumn('absensi', 'dari_admin')) {
                $table->boolean('dari_admin')->default(false)->after('metode');
            }
            if (! Schema::hasColumn('absensi', 'aktif')) {
                $table->boolean('aktif')->default(true)->after('dari_admin');
            }
        });

        Schema::table('absensi_detail', function (Blueprint $table) {
            if (! Schema::hasColumn('absensi_detail', 'dicatat_oleh')) {
                $table->foreignId('dicatat_oleh')->nullable()->after('waktu')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('absensi_detail', function (Blueprint $table) {
            if (Schema::hasColumn('absensi_detail', 'dicatat_oleh')) {
                $table->dropConstrainedForeignId('dicatat_oleh');
            }
        });
        Schema::table('absensi', function (Blueprint $table) {
            foreach (['aktif', 'dari_admin'] as $kolom) {
                if (Schema::hasColumn('absensi', $kolom)) {
                    $table->dropColumn($kolom);
                }
            }
        });
    }
};
