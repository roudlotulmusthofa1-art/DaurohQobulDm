<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Kolom "gedung" dan "jenis" di Master Data Kamar sudah tidak dipakai, jadi dihapus. */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['gedung', 'jenis'] as $kolom) {
            if (Schema::hasColumn('kamar', $kolom)) {
                Schema::table('kamar', fn (Blueprint $table) => $table->dropColumn($kolom));
            }
        }
    }

    // Kalau dibatalkan (php artisan migrate:rollback), kolomnya dibuat lagi (isinya kosong / bawaan)
    public function down(): void
    {
        if (! Schema::hasColumn('kamar', 'gedung')) {
            Schema::table('kamar', fn (Blueprint $table) => $table->string('gedung', 100)->nullable());
        }
        if (! Schema::hasColumn('kamar', 'jenis')) {
            Schema::table('kamar', fn (Blueprint $table) => $table->enum('jenis', ['putra', 'putri'])->default('putra'));
        }
    }
};
