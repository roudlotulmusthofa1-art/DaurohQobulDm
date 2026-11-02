<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Sistem > Role & Permission
 * - Tabel "roles" disimpan di database UTAMA (koneksi "pusat"), dipakai semua sesi.
 *   Isi awal: 3 role bawaan (admin, asatid, siswa) yang tidak bisa dihapus.
 *   Admin bisa menambah role pengurus baru (mis. Bendahara) dan memilih menu admin yang boleh dibuka.
 * - Kolom users.role_id: role pengurus untuk akun admin. Kosong = Super Admin (semua menu).
 *   Migrasi ini juga dijalankan di database tiap sesi (php artisan sesi:migrate), jadi kolom ini ada di semua database.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::connection('pusat')->hasTable('roles')) {
            Schema::connection('pusat')->create('roles', function (Blueprint $t) {
                $t->id();
                $t->string('kunci', 40)->unique();       // admin, asatid, siswa, atau buatan sendiri (mis. bendahara)
                $t->string('nama', 80);
                $t->string('keterangan', 255)->nullable();
                $t->string('warna', 20)->default('slate'); // warna lencana di tampilan
                $t->boolean('bawaan')->default(false);   // role bawaan tidak bisa dihapus
                $t->json('izin')->nullable();            // daftar menu admin yang boleh dibuka
                $t->timestamps();
            });

            $sekarang = now();
            DB::connection('pusat')->table('roles')->insert([
                ['kunci' => 'admin', 'nama' => 'Super Admin', 'keterangan' => 'Bisa membuka semua menu panel admin.', 'warna' => 'emerald', 'bawaan' => true, 'izin' => null, 'created_at' => $sekarang, 'updated_at' => $sekarang],
                ['kunci' => 'asatid', 'nama' => 'Asatid', 'keterangan' => 'Pengajar. Membuka dasbor asatid: jadwal, absensi, peserta, penilaian.', 'warna' => 'sky', 'bawaan' => true, 'izin' => null, 'created_at' => $sekarang, 'updated_at' => $sekarang],
                ['kunci' => 'siswa', 'nama' => 'Peserta', 'keterangan' => 'Peserta dauroh. Membuka dasbor siswa: profil, kelulusan, pesan.', 'warna' => 'amber', 'bawaan' => true, 'izin' => null, 'created_at' => $sekarang, 'updated_at' => $sekarang],
            ]);
        }

        if (! Schema::hasColumn('users', 'role_id')) {
            Schema::table('users', fn (Blueprint $t) => $t->unsignedBigInteger('role_id')->nullable()->after('role'));
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'role_id')) {
            Schema::table('users', fn (Blueprint $t) => $t->dropColumn('role_id'));
        }

        // Hanya dihapus dari database utama
        if (DB::connection()->getDatabaseName() === DB::connection('pusat')->getDatabaseName()) {
            Schema::connection('pusat')->dropIfExists('roles');
        }
    }
};
