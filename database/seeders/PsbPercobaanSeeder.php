<?php

namespace Database\Seeders;

use App\Models\PendaftaranSiswa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * 5 pendaftar PSB contoh (status "menunggu"), untuk percobaan di Data Masuk Peserta.
 *
 * Jalankan:  php artisan db:seed --class=PsbPercobaanSeeder
 *
 * - Email: percobaan1@contoh.test s/d percobaan5@contoh.test, password: percobaan123
 * - Tanpa berkas (foto, rekomendasi, dll. kosong).
 * - Aman dijalankan berkali-kali: email yang sudah ada dilewati.
 * - Tidak bisa dijalankan di server production, dan tidak dipanggil dari DatabaseSeeder.
 */
class PsbPercobaanSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->warn('Dilewati: data percobaan PSB tidak boleh masuk ke server production.');

            return;
        }

        $peserta = [
            ['Ahmad Fauzi',      'أحمد فوزي',      'جاكرتا',   '2004-03-12', 'جاوى الغربية', 'باندونغ',  'نحو',  'الأجرومية'],
            ['Muhammad Rizki',   'محمد رزقي',      'سورابايا', '2003-07-25', 'جاوى الشرقية', 'سورابايا', 'فقه',  'سفينة النجا'],
            ['Abdullah Hasan',   'عبد الله حسن',    'مالانج',   '2005-01-08', 'جاوى الشرقية', 'مالانج',   'توحيد', 'عقيدة العوام'],
            ['Umar Al-Faruq',    'عمر الفاروق',     'سمارانغ',  '2004-11-30', 'جاوى الوسطى',  'سمارانغ',  'صرف',  'الأمثلة التصريفية'],
            ['Ali Zainal Abidin', 'علي زين العابدين', 'ميدان',    '2003-05-17', 'سومطرة الشمالية', 'ميدان', 'تجويد', 'تحفة الأطفال'],
        ];

        $dibuat = 0;
        $kataSandi = Hash::make('percobaan123');

        foreach ($peserta as $i => [$nama, $arab, $tempat, $lahir, $provinsi, $kota, $fan, $kitab]) {
            $email = 'percobaan' . ($i + 1) . '@contoh.test';
            if (PendaftaranSiswa::where('email', $email)->exists()) {
                continue;
            }

            DB::transaction(function () use ($i, $nama, $arab, $tempat, $lahir, $provinsi, $kota, $fan, $kitab, $email, $kataSandi) {
                // Kode QR dibuat otomatis oleh model saat data baru disimpan
                PendaftaranSiswa::create([
                    'nomor_pendaftaran' => PendaftaranSiswa::buatNomor(),
                    'nama_lengkap'      => $nama,
                    'nama_lengkap_arab' => $arab,
                    'tempat_lahir'      => $tempat,
                    'tanggal_lahir'     => $lahir,
                    'status_pernikahan' => 'L',
                    'no_hp'             => '+62812345678' . ($i + 1) . '0',
                    'provinsi'          => $provinsi,
                    'kota'              => $kota,
                    'kecamatan'         => 'المركز',
                    'kelurahan'         => 'القرية',
                    'alamat'            => 'شارع السلام رقم ' . ($i + 1),
                    'nama_wali'         => 'والد ' . $arab,
                    'pekerjaan_wali'    => 'تاجر',
                    'hubungan_wali'     => 'أب',
                    'no_hp_wali'        => '+62813456789' . ($i + 1) . '0',
                    'riwayat_pondok'    => [[
                        'asal_pondok'   => 'معهد دار السلام',
                        'nama_pengasuh' => 'الشيخ عبد الرحمن',
                        'nama_daerah'   => $kota,
                        'tahun_belajar' => 3 + $i,
                    ]],
                    'riwayat_kitab'     => [[
                        'nama_kitab'     => $kitab,
                        'fan_kitab'      => $fan,
                        'sudah_diujikan' => $i % 2 ? 'sudah' : 'belum',
                    ]],
                    'pendidikan_terakhir' => ['K', 'L', 'M', 'o', 'L'][$i],
                    'nama_sekolah'      => 'المدرسة الثانوية',
                    'nama_daerah'       => $kota,
                    'marhalah'          => 'ثانوية',
                    'lama_belajar'      => (3 + $i) . ' سنوات',
                    'kesehatan'         => 'سليم',
                    'status_ekonomi'    => 'متوسط',
                    'persentasimuslim'  => 90,
                    'alumni'            => 'لا',
                    'tujuan'            => 'طلب العلم',
                    'rencana'           => 'التدريس بعد التخرج',
                    'catatan_peserta'   => $i === 0 ? 'Data percobaan dari seeder.' : null,
                    'email'             => $email,
                    'password'          => $kataSandi,
                    'setuju'            => true,
                    'status'            => PendaftaranSiswa::MENUNGGU,
                    'syarat_no_paspor'  => '0',
                ]);
            });

            $dibuat++;
        }

        $this->command?->info("Pendaftar PSB percobaan: {$dibuat} dibuat (yang sudah ada dilewati).");
    }
}
