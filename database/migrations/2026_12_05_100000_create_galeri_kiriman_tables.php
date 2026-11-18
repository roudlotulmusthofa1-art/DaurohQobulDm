<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Galeri Foto bentuk ALBUM (database UTAMA, koneksi "pusat"):
 * - galeri_kiriman   : satu album (keterangan, tanggal, pengirim, status menunggu / tampil / ditolak)
 *                      dikirim admin (langsung tampil) atau asatid & siswa (menu Kirim Foto, diperiksa admin)
 * - galeri_foto      : + kolom kiriman_id & urutan (foto milik album)
 * - galeri_suka      : tanda suka album, satu per browser (kunci = kode acak dari cookie)
 * - galeri_komentar  : komentar album (+ balasan: induk_id, hanya admin & pengirim album)
 * Foto galeri lama (sebelum ada album) masing-masing dijadikan satu album yang langsung tampil.
 */
return new class extends Migration
{
    public function up(): void
    {
        $pusat = Schema::connection('pusat');
        if (! $pusat->hasTable('galeri_foto')) {
            return; // migrasi galeri_foto belum jalan di database ini
        }

        if (! $pusat->hasTable('galeri_kiriman')) {
            $pusat->create('galeri_kiriman', function (Blueprint $t) {
                $t->id();
                $t->string('keterangan', 500)->nullable();
                $t->date('tanggal')->nullable();               // tanggal kegiatan (kosong = tanggal kirim)
                // Pengirim (disalin, supaya tetap tampil walau akunnya pindah sesi / dihapus)
                $t->unsignedBigInteger('penulis_user_id')->nullable();
                $t->string('penulis_peran', 20);               // admin | asatid | siswa
                $t->string('penulis_nama', 150);
                $t->string('penulis_foto', 500)->nullable();
                $t->unsignedBigInteger('sesi_id')->nullable();
                // Pemeriksaan admin
                $t->string('status', 20)->default('menunggu'); // menunggu | tampil | ditolak
                $t->timestamp('tampil_mulai')->nullable();
                $t->text('catatan_admin')->nullable();
                $t->string('diperiksa_oleh', 150)->nullable();
                $t->timestamp('diperiksa_at')->nullable();
                $t->unsignedInteger('jumlah_dilihat')->default(0);
                $t->unsignedInteger('jumlah_suka')->default(0);
                $t->timestamps();

                $t->index('status');
                $t->index(['penulis_peran', 'penulis_user_id']);
            });
        }

        if (! $pusat->hasColumn('galeri_foto', 'kiriman_id')) {
            $pusat->table('galeri_foto', function (Blueprint $t) {
                $t->foreignId('kiriman_id')->nullable()->after('id')->constrained('galeri_kiriman')->cascadeOnDelete();
                $t->unsignedSmallInteger('urutan')->default(0)->after('path');
            });
        }

        if (! $pusat->hasTable('galeri_suka')) {
            $pusat->create('galeri_suka', function (Blueprint $t) {
                $t->id();
                $t->foreignId('kiriman_id')->constrained('galeri_kiriman')->cascadeOnDelete();
                $t->string('kunci', 64);
                $t->timestamps();
                $t->unique(['kiriman_id', 'kunci']);
            });
        }

        if (! $pusat->hasTable('galeri_komentar')) {
            $pusat->create('galeri_komentar', function (Blueprint $t) {
                $t->id();
                $t->foreignId('kiriman_id')->constrained('galeri_kiriman')->cascadeOnDelete();
                $t->foreignId('induk_id')->nullable()->constrained('galeri_komentar')->cascadeOnDelete();
                $t->string('nama', 100);
                $t->text('isi');
                $t->unsignedBigInteger('user_id')->nullable();
                $t->string('peran', 20)->nullable();
                $t->string('kunci', 64)->nullable();
                $t->string('ip', 45)->nullable();
                $t->timestamps();
            });
        }

        // Foto lama tanpa album -> satu album per foto, langsung tampil
        $db = DB::connection('pusat');
        foreach ($db->table('galeri_foto')->whereNull('kiriman_id')->orderBy('id')->get() as $f) {
            $id = $db->table('galeri_kiriman')->insertGetId([
                'keterangan' => $f->keterangan,
                'tanggal' => $f->tanggal,
                'penulis_peran' => 'admin',
                'penulis_nama' => $f->diunggah_oleh ?: 'Admin',
                'status' => 'tampil',
                'tampil_mulai' => $f->created_at,
                'created_at' => $f->created_at,
                'updated_at' => $f->updated_at,
            ]);
            $db->table('galeri_foto')->where('id', $f->id)->update(['kiriman_id' => $id, 'urutan' => 1]);
        }
    }

    public function down(): void
    {
        $pusat = Schema::connection('pusat');
        if (DB::connection()->getDatabaseName() !== DB::connection('pusat')->getDatabaseName()) {
            return;
        }
        $pusat->dropIfExists('galeri_komentar');
        $pusat->dropIfExists('galeri_suka');
        if ($pusat->hasColumn('galeri_foto', 'kiriman_id')) {
            $pusat->table('galeri_foto', function (Blueprint $t) {
                $t->dropConstrainedForeignId('kiriman_id');
                $t->dropColumn('urutan');
            });
        }
        $pusat->dropIfExists('galeri_kiriman');
    }
};
