<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ujian: bulan ajaran yang dicakup satu ujian (boleh 1, 2, 3 bulan atau lebih), dipilih admin di Pengaturan Ujian.
 * Nama ujian (ujian.nama) diisi otomatis dari nama bulannya, mis. "Muharram, Rabi'ul Akhir".
 * Data milik sesi (angkatan), jadi ikut dijalankan di database tiap sesi (php artisan sesi:migrate).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ujian_bulan')) {
            Schema::create('ujian_bulan', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ujian_id')->constrained('ujian')->cascadeOnDelete();
                $table->foreignId('bulan_ajaran_id')->constrained('bulan_ajaran')->cascadeOnDelete();
                $table->unique(['ujian_id', 'bulan_ajaran_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ujian_bulan');
    }
};
