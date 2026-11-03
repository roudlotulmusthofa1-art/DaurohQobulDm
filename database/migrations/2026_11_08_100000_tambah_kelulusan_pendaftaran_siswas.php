<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kelulusan akhir dauroh (BUKAN hasil seleksi pendaftaran).
 * Diisi admin di menu Akademik > Kelulusan, lalu dilihat peserta di dasbor > Pengumuman Kelulusan
 * setelah admin menekan "Umumkan" (waktunya disimpan di tabel pengaturan: kelulusan_diumumkan_at).
 * Data milik sesi (angkatan), jadi ikut dijalankan di database tiap sesi (php artisan sesi:migrate).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pendaftaran_siswas', function (Blueprint $t) {
            if (! Schema::hasColumn('pendaftaran_siswas', 'status_kelulusan')) {
                $t->string('status_kelulusan', 20)->nullable()->after('nomor_kasur'); // lulus | tidak_lulus | null = belum diisi
            }
            if (! Schema::hasColumn('pendaftaran_siswas', 'predikat_kelulusan')) {
                $t->string('predikat_kelulusan', 50)->nullable()->after('status_kelulusan'); // mis. Mumtaz, Jayyid Jiddan
            }
            if (! Schema::hasColumn('pendaftaran_siswas', 'catatan_kelulusan')) {
                $t->text('catatan_kelulusan')->nullable()->after('predikat_kelulusan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pendaftaran_siswas', function (Blueprint $t) {
            $t->dropColumn(['status_kelulusan', 'predikat_kelulusan', 'catatan_kelulusan']);
        });
    }
};
