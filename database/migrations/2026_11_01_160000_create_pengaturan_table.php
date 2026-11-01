<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel pengaturan: menyimpan nilai-nilai kecil yang bisa diubah admin
 * (misalnya jumlah assatid yang tampil di beranda). Satu baris = satu pengaturan.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pengaturan')) {
            Schema::create('pengaturan', function (Blueprint $t) {
                $t->id();
                $t->string('kunci')->unique();
                $t->text('nilai')->nullable();
                $t->timestamps();
            });
        }

        // Nilai awal = angka yang sebelumnya tertulis di beranda
        if (! DB::table('pengaturan')->where('kunci', 'jumlah_assatid')->exists()) {
            DB::table('pengaturan')->insert([
                'kunci'      => 'jumlah_assatid',
                'nilai'      => '15',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan');
    }
};
