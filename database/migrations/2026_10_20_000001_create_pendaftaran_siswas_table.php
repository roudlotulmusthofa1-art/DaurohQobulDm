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

                $t->json('riwayat_pondok')->nullable();
                $t->json('riwayat_kitab')->nullable();
                $t->timestamps();
            });
        }

        $kolom = [
            'kode_qr' => fn(Blueprint $t) => $t->string('kode_qr', 32)->nullable()->unique()->after('nomor_pendaftaran'),
            // STEP 1 - Data pribadi
            'nama_lengkap'   => fn (Blueprint $t) => $t->string('nama_lengkap')->nullable(),
            'nama_lengkap_arab' => fn (Blueprint $t) => $t->string('nama_lengkap_arab')->nullable(),
            'tempat_lahir'   => fn (Blueprint $t) => $t->string('tempat_lahir')->nullable(),
            'tanggal_lahir'  => fn (Blueprint $t) => $t->date('tanggal_lahir')->nullable(),
            'status_pernikahan' => fn (Blueprint $t) => $t->enum('status_pernikahan', ['K', 'L'])->nullable(),
            'no_hp' => fn (Blueprint $t) => $t->string('no_hp', 20)->nullable(),
            // STEP 2 - Alamat
            'provinsi'       => fn (Blueprint $t) => $t->string('provinsi')->nullable(),
            'kota'           => fn (Blueprint $t) => $t->string('kota')->nullable(),
            'kecamatan'      => fn (Blueprint $t) => $t->string('kecamatan')->nullable(),
            'kelurahan'      => fn (Blueprint $t) => $t->string('kelurahan')->nullable(),
            'alamat'         => fn (Blueprint $t) => $t->text('alamat')->nullable(),
            // STEP 3 - Data orang tua / wali
            'nama_wali'      => fn (Blueprint $t) => $t->string('nama_wali')->nullable(),
            'pekerjaan_wali' => fn (Blueprint $t) => $t->string('pekerjaan_wali')->nullable(),
            'hubungan_wali'  => fn (Blueprint $t) => $t->string('hubungan_wali')->nullable(),
            'no_hp_wali'     => fn (Blueprint $t) => $t->string('no_hp_wali', 20)->nullable(),
            'nama_muarrif'   => fn (Blueprint $t) => $t->string('nama_muarrif')->nullable(),
            'no_hp_muarrif'  => fn (Blueprint $t) => $t->string('no_hp_muarrif', 20)->nullable(),
            'nama_butuh'     => fn (Blueprint $t) => $t->string('nama_butuh')->nullable(),
            'no_hp_nya'      => fn (Blueprint $t) => $t->string('no_hp_nya', 20)->nullable(),
            // STEP 4 - Pendidikan & program
            'pendidikan_terakhir' => fn (Blueprint $t) => $t->enum('pendidikan_terakhir', ['K', 'L', 'M', 'o'])->nullable(),
            'nama_sekolah'     => fn (Blueprint $t) => $t->string('nama_sekolah')->nullable(),
            'nama_daerah'     => fn (Blueprint $t) => $t->string('nama_daerah')->nullable(),
            // STEP 5 - Data tambahan
            'marhalah' => fn (Blueprint $t) => $t->string('marhalah')->nullable(),
            'lama_belajar' => fn (Blueprint $t) => $t->string('lama_belajar')->nullable(),
            'kesehatan' => fn (Blueprint $t) => $t->string('kesehatan')->nullable(),
            'status_ekonomi' => fn (Blueprint $t) => $t->string('status_ekonomi')->nullable(),
            'persentasimuslim' => fn (Blueprint $t) => $t->decimal('persentasimuslim', 5, 2)->nullable(),
            'alumni' => fn (Blueprint $t) => $t->string('alumni')->nullable(),
            'tujuan' => fn (Blueprint $t) => $t->string('tujuan')->nullable(),
            'rencana' => fn (Blueprint $t) => $t->string('rencana')->nullable(),
            'email' => fn(Blueprint $t) => $t->string('email')->nullable()->unique(),
            'password' => fn(Blueprint $t) => $t->string('password')->nullable(),
            // STEP 6 - Berkas
            'berkas_foto' => fn (Blueprint $t) => $t->string('berkas_foto')->nullable(),
            'berkas_rekomendasi' => fn (Blueprint $t) => $t->string('berkas_rekomendasi')->nullable(),
            'berkas_raport_ijazah' => fn (Blueprint $t) => $t->string('berkas_raport_ijazah')->nullable(),
            'berkas_izin_belajar' => fn (Blueprint $t) => $t->string('berkas_izin_belajar')->nullable(),
            'setuju'         => fn (Blueprint $t) => $t->boolean('setuju')->default(false),
            // Status & proses admin
            'status'         => fn (Blueprint $t) => $t->string('status', 20)->default('menunggu'),
            'catatan_admin'  => fn (Blueprint $t) => $t->text('catatan_admin')->nullable(),
            // Persyaratan (dicentang admin)
            'syarat_uang_pendaftaran' => fn(Blueprint $t) => $t->boolean('syarat_uang_pendaftaran')->default(false),
            'syarat_rekomendasi'      => fn(Blueprint $t) => $t->boolean('syarat_rekomendasi')->default(false),
            'syarat_izin_orang_tua'   => fn(Blueprint $t) => $t->boolean('syarat_izin_orang_tua')->default(false),
            'syarat_raport'           => fn(Blueprint $t) => $t->boolean('syarat_raport')->default(false),
            'syarat_paspor'           => fn(Blueprint $t) => $t->boolean('syarat_paspor')->default(false),
            'syarat_no_paspor'        => fn(Blueprint $t) => $t->string('syarat_no_paspor')->default(false),
            'kelas'            => fn(Blueprint $t) => $t->unsignedTinyInteger('kelas')->nullable(),
            'kamar'            => fn(Blueprint $t) => $t->unsignedTinyInteger('kamar')->nullable(),
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
