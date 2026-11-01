<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Kolom "tingkat" di Master Data Kelas sudah tidak dipakai, jadi dihapus. */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('kelas', 'tingkat')) {
            Schema::table('kelas', fn(Blueprint $table) => $table->dropColumn('tingkat'));
        }
    }

    // Kalau dibatalkan (php artisan migrate:rollback), kolomnya dibuat lagi (isinya kosong)
    public function down(): void
    {
        if (! Schema::hasColumn('kelas', 'tingkat')) {
            Schema::table('kelas', fn(Blueprint $table) => $table->unsignedTinyInteger('tingkat')->nullable());
        }
    }
};
