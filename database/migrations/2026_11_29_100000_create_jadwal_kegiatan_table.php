<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Absensi Kegiatan BERULANG (seperti alarm HP).
 * - jadwal_kegiatan          : kegiatan + hari (json 1=Senin..7=Ahad) + jam mulai/terlambat/alfa + metode + saklar nyala.
 *                              Pada hari yang dipilih, absensi kegiatan dibuat otomatis & langsung aktif (JadwalKegiatan::buatHariIni).
 *                              terakhir_dibuat = tanggal terakhir dibuat, supaya absensi yang dihapus admin tidak dibuat ulang hari itu.
 * - absensi.jadwal_kegiatan_id : absensi ini dibuat otomatis dari jadwal mana (null = dibuat sekali / manual).
 * Data milik sesi (angkatan), jadi ikut dijalankan di database tiap sesi (php artisan sesi:migrate).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('jadwal_kegiatan')) {
            Schema::create('jadwal_kegiatan', function (Blueprint $table) {
                $table->id();
                $table->foreignId('jenis_kegiatan_id')->constrained('jenis_kegiatan')->cascadeOnDelete();
                $table->json('hari');                         // [1..7], 1 = Senin, 7 = Ahad
                $table->time('jam_mulai');
                $table->time('batas_terlambat');
                $table->time('batas_alfa');
                $table->string('metode', 10)->default('qr');  // qr | manual
                $table->boolean('nyala')->default(true);      // saklar ON/OFF
                $table->date('terakhir_dibuat')->nullable();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('absensi') && ! Schema::hasColumn('absensi', 'jadwal_kegiatan_id')) {
            Schema::table('absensi', function (Blueprint $table) {
                $table->foreignId('jadwal_kegiatan_id')->nullable()->after('jenis_kegiatan_id')
                    ->constrained('jadwal_kegiatan')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('absensi', 'jadwal_kegiatan_id')) {
            Schema::table('absensi', function (Blueprint $table) {
                $table->dropConstrainedForeignId('jadwal_kegiatan_id');
            });
        }
        Schema::dropIfExists('jadwal_kegiatan');
    }
};
