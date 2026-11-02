<?php

namespace Database\Seeders;

use App\Models\PendaftaranAsatid;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * 3 asatid percobaan untuk menu Manajemen Data > Data Asatid:
 * - 2 sudah DITERIMA  -> langsung punya akun login role asatid (bisa dipilih di Jadwal Mengajar)
 * - 1 masih MENUNGGU  -> untuk mencoba tombol Terima / Tolak
 *
 * Jalankan:  php artisan db:seed --class=AsatidPercobaanSeeder
 *
 * - Email: asatid1@contoh.test s/d asatid3@contoh.test, password: percobaan123
 * - Tanpa pas foto. Tidak ada pesan WhatsApp yang dikirim.
 * - Aman dijalankan berkali-kali: email yang sudah ada dilewati.
 * - Tidak dipanggil dari DatabaseSeeder dan tidak berjalan di server production.
 */
class AsatidPercobaanSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->warn('Dilewati: data percobaan asatid tidak boleh masuk ke server production.');

            return;
        }

        $asatid = [
            // nama, nama arab, tempat lahir, tanggal lahir, alamat, lama belajar, tahun selesai, diterima?
            ['Ustadz Muhammad Haidar', 'محمد حيدر', 'جاكرتا', '1990-04-12', 'شارع الهداية رقم 5', '5 tahun', '2015', true],
            ['Ustadz Fathur Rohim', 'فتح الرحيم', 'سورابايا', '1992-08-03', 'شارع النور رقم 12', '4 tahun', '2017', true],
            ['Ustadz Abubakar Rohidi', 'أبو بكر راحدي', 'مالانج', '1994-01-20', 'شارع السلام رقم 8', '3 tahun', '2019', false],
        ];

        $kataSandi = Hash::make('percobaan123');
        $dibuat = 0;

        foreach ($asatid as $i => [$nama, $arab, $tempat, $lahir, $alamat, $lama, $selesai, $diterima]) {
            $email = 'asatid' . ($i + 1) . '@contoh.test';
            if (PendaftaranAsatid::where('email', $email)->exists()) {
                continue;
            }

            DB::connection('pusat')->transaction(function () use ($i, $nama, $arab, $tempat, $lahir, $alamat, $lama, $selesai, $diterima, $email, $kataSandi) {
                $a = PendaftaranAsatid::create([
                    'nomor_pendaftaran' => PendaftaranAsatid::buatNomor(),
                    'nama_lengkap'      => $nama,
                    'nama_lengkap_arab' => $arab,
                    'tempat_lahir'      => $tempat,
                    'tanggal_lahir'     => $lahir,
                    'no_hp'             => '+62815000000' . ($i + 1) . '0',
                    'alamat'            => $alamat,
                    'lama_belajar'      => $lama,
                    'tahun_selesai'     => $selesai,
                    'email'             => $email,
                    'password'          => $kataSandi,
                    'catatan_peserta'   => $i === 0 ? 'Data percobaan dari seeder. Bisa mengajar nahwu dan fiqih.' : null,
                    'status'            => PendaftaranAsatid::MENUNGGU,
                ]);

                // Diterima -> akun di tabel users dengan role asatid ikut dibuat (tanpa kirim WhatsApp)
                if ($diterima) {
                    $a->terima('Diterima lewat data percobaan.');
                }
            });

            $dibuat++;
        }

        $this->command?->info("Asatid percobaan: {$dibuat} dibuat (yang sudah ada dilewati).");
    }
}
