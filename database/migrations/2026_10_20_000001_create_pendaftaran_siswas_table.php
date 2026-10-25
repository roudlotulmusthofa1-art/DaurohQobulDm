<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel pendaftaran santri (PSB).
 * Aman dijalankan walau tabel sudah ada: kolom yang belum ada akan ditambahkan.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pendaftaran_siswas')) {
            Schema::create('pendaftaran_siswas', function (Blueprint $t) {
                $t->id();
                $t->string('nomor_pendaftaran')->unique();
                $t->timestamps();
            });
        }

        $kolom = [
            // STEP 1 - Data pribadi
            'nama_lengkap'   => fn (Blueprint $t) => $t->string('nama_lengkap')->nullable(),
            'nama_lengkap_arab' => fn (Blueprint $t) => $t->string('nama_lengkap_arab')->nullable(),
            'nik'            => fn (Blueprint $t) => $t->string('nik', 16)->nullable(),
            'nisn'           => fn (Blueprint $t) => $t->string('nisn', 10)->nullable(),
            'jenis_kelamin'  => fn (Blueprint $t) => $t->enum('jenis_kelamin', ['L', 'P'])->nullable(),
            'tempat_lahir'   => fn (Blueprint $t) => $t->string('tempat_lahir')->nullable(),
            'tanggal_lahir'  => fn (Blueprint $t) => $t->date('tanggal_lahir')->nullable(),
            'anak_ke'        => fn (Blueprint $t) => $t->unsignedTinyInteger('anak_ke')->nullable(),
            'jumlah_saudara' => fn (Blueprint $t) => $t->unsignedTinyInteger('jumlah_saudara')->nullable(),
            // STEP 2 - Alamat
            'alamat'         => fn (Blueprint $t) => $t->text('alamat')->nullable(),
            'provinsi'       => fn (Blueprint $t) => $t->string('provinsi')->nullable(),
            'kota'           => fn (Blueprint $t) => $t->string('kota')->nullable(),
            'kecamatan'      => fn (Blueprint $t) => $t->string('kecamatan')->nullable(),
            'kelurahan'      => fn (Blueprint $t) => $t->string('kelurahan')->nullable(),
            'kode_pos'       => fn (Blueprint $t) => $t->string('kode_pos', 10)->nullable(),
            // STEP 3 - Orang tua / wali
            'nama_ayah'      => fn (Blueprint $t) => $t->string('nama_ayah')->nullable(),
            'pekerjaan_ayah' => fn (Blueprint $t) => $t->string('pekerjaan_ayah')->nullable(),
            'nama_ibu'       => fn (Blueprint $t) => $t->string('nama_ibu')->nullable(),
            'pekerjaan_ibu'  => fn (Blueprint $t) => $t->string('pekerjaan_ibu')->nullable(),
            'nama_wali'      => fn (Blueprint $t) => $t->string('nama_wali')->nullable(),
            'no_hp_wali'     => fn (Blueprint $t) => $t->string('no_hp_wali', 20)->nullable(),
            // STEP 4 - Pendidikan & program
            'asal_sekolah'    => fn (Blueprint $t) => $t->string('asal_sekolah')->nullable(),
            'tahun_lulus'     => fn (Blueprint $t) => $t->string('tahun_lulus', 4)->nullable(),
            'program_pilihan' => fn (Blueprint $t) => $t->string('program_pilihan')->nullable(),
            'hafalan_juz'     => fn (Blueprint $t) => $t->unsignedTinyInteger('hafalan_juz')->default(0),
            // STEP 5 - Berkas
            'berkas_foto'    => fn (Blueprint $t) => $t->string('berkas_foto')->nullable(),
            'berkas_kk'      => fn (Blueprint $t) => $t->string('berkas_kk')->nullable(),
            'berkas_akta'    => fn (Blueprint $t) => $t->string('berkas_akta')->nullable(),
            'setuju'         => fn (Blueprint $t) => $t->boolean('setuju')->default(false),
            // Status & proses admin
            'status'         => fn (Blueprint $t) => $t->string('status', 20)->default('menunggu'),
            'catatan_admin'  => fn (Blueprint $t) => $t->text('catatan_admin')->nullable(),
            'diproses_at'    => fn (Blueprint $t) => $t->timestamp('diproses_at')->nullable(),
            // Akun siswa (login: nomor_pendaftaran + kode akses, disimpan hash)
            // 'password_siswa' => fn (Blueprint $t) => $t->string('password_siswa')->nullable(),
            'user_id' => fn(Blueprint $t) => $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(),
        ];

        foreach ($kolom as $nama => $buat) {
            if (! Schema::hasColumn('pendaftaran_siswas', $nama)) {
                Schema::table('pendaftaran_siswas', fn (Blueprint $t) => $buat($t));
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pendaftaran_siswas');
    }
};
