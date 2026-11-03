<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Status notifikasi lonceng per akun: sudah dibaca / sudah dihapus.
 * Notifikasi sendiri tetap dihitung langsung dari data; tabel ini hanya mengingat
 * "kunci" notifikasi yang sudah dibaca atau dihapus oleh tiap akun.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('notifikasi_status')) {
            Schema::create('notifikasi_status', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('kunci', 150);              // mis. pendaftar_baru:12
                $table->timestamp('dibaca_at')->nullable();
                $table->timestamp('dihapus_at')->nullable();
                $table->timestamps();
                $table->unique(['user_id', 'kunci']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi_status');
    }
};
