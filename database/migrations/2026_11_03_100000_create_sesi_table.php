<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Daftar sesi (angkatan/gelombang dauroh). Selalu disimpan di database UTAMA (koneksi "pusat").
 * - Sesi "utama" memakai database utama itu sendiri (kolom database = null) dan folder storage/app/public.
 * - Sesi lain punya database sendiri (mis. dauroh_qobul_dm_sesi_2) dan folder storage/app/public/sesi/{kode}.
 *
 * Migrasi ini juga ikut dijalankan di database sesi lain (php artisan sesi:migrate),
 * jadi tabel hanya dibuat bila belum ada di database utama.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('pusat')->hasTable('sesi')) {
            return;
        }

        Schema::connection('pusat')->create('sesi', function (Blueprint $t) {
            $t->id();
            $t->string('nama', 150);
            $t->string('kode', 60)->unique();          // dipakai untuk nama folder file
            $t->string('database', 64)->nullable();    // null = database utama
            $t->date('tanggal_mulai')->nullable();
            $t->date('tanggal_selesai')->nullable();
            $t->text('keterangan')->nullable();
            $t->boolean('is_active')->default(false);
            $t->timestamps();
        });

        DB::connection('pusat')->table('sesi')->insert([
            'nama' => 'Sesi Utama',
            'kode' => 'utama',
            'database' => null,
            'keterangan' => 'Data yang sudah ada sebelum fitur sesi dibuat.',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // Hanya dihapus dari database utama
        if (DB::connection()->getDatabaseName() === DB::connection('pusat')->getDatabaseName()) {
            Schema::connection('pusat')->dropIfExists('sesi');
        }
    }
};
