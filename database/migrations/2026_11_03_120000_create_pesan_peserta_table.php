<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pesan antara admin dan peserta (dasbor siswa: "Pesan dari Admin" & "Pesan untuk Admin").
 * - dari = 'admin'   : pesan khusus dari admin untuk satu peserta (pesan massal disalin per peserta)
 * - dari = 'peserta' : pesan peserta untuk admin
 * - balasan_untuk_id : pesan yang dibalas (admin membalas pesan peserta)
 * - dibaca_at        : kapan dibaca oleh penerimanya
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesan_peserta', function (Blueprint $t) {
            $t->id();
            $t->foreignId('pendaftaran_siswa_id')->constrained('pendaftaran_siswas')->cascadeOnDelete();
            $t->string('dari', 10);
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // admin pengirim
            $t->foreignId('balasan_untuk_id')->nullable()->constrained('pesan_peserta')->nullOnDelete();
            $t->string('judul', 150)->nullable();
            $t->text('isi');
            $t->timestamp('dibaca_at')->nullable();
            $t->timestamps();

            $t->index(['pendaftaran_siswa_id', 'dari', 'dibaca_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesan_peserta');
    }
};
