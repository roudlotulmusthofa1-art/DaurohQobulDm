<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\PendaftaranSiswa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * 10 pendaftar PSB contoh untuk percobaan:
 * - 8 pertama  : DITERIMA (diproses seperti tombol "Terima" di admin: akun login dibuat, dibagi ke kelas yang ada)
 * - 2 terakhir : MENUNGGU verifikasi (muncul di PSB > Data Masuk Peserta)
 *
 * Jalankan:  php artisan db:seed --class=PsbPercobaanSeeder
 *
 * - Email: percobaan1@contoh.test s/d percobaan10@contoh.test, password: percobaan123
 *   (semua bisa login ke dasbor siswa)
 * - Tanpa berkas (foto, rekomendasi, dll. kosong).
 * - Aman dijalankan berkali-kali: email yang sudah ada dilewati.
 * - Tidak bisa dijalankan di server production, dan tidak dipanggil dari DatabaseSeeder.
 */
class PsbPercobaanSeeder extends Seeder
{
    /** Berapa pendaftar pertama yang langsung diterima */
    private const JUMLAH_DITERIMA = 8;

    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->warn('Dilewati: data percobaan PSB tidak boleh masuk ke server production.');

            return;
        }

        // nama, nama Arab, tempat lahir, tanggal lahir, provinsi, kota, fan, kitab
        $peserta = [
            ['Ahmad Fauzi',        'أحمد فوزي',         'جاكرتا',    '2004-03-12', 'جاوى الغربية',    'باندونغ',   'نحو',   'الأجرومية'],
            ['Muhammad Rizki',     'محمد رزقي',         'سورابايا',  '2003-07-25', 'جاوى الشرقية',    'سورابايا',  'فقه',   'سفينة النجا'],
            ['Abdullah Hasan',     'عبد الله حسن',       'مالانج',    '2005-01-08', 'جاوى الشرقية',    'مالانج',    'توحيد', 'عقيدة العوام'],
            ['Umar Al-Faruq',      'عمر الفاروق',        'سمارانغ',   '2004-11-30', 'جاوى الوسطى',     'سمارانغ',   'صرف',   'الأمثلة التصريفية'],
            ['Ali Zainal Abidin',  'علي زين العابدين',    'ميدان',     '2003-05-17', 'سومطرة الشمالية', 'ميدان',     'تجويد', 'تحفة الأطفال'],
            ['Hasan Basri',        'حسن البصري',         'بوغور',     '2004-08-21', 'جاوى الغربية',    'بوغور',     'حديث',  'الأربعين النووية'],
            ['Husain Al-Habsyi',   'حسين الحبشي',        'سولو',      '2005-02-14', 'جاوى الوسطى',     'سولو',      'سيرة',  'خلاصة نور اليقين'],
            ['Salim Bahreisy',     'سالم بحريسي',        'بانجرماسين', '2003-12-03', 'كاليمانتان الجنوبية', 'بانجرماسين', 'أخلاق', 'تعليم المتعلم'],
            ['Yusuf Mansur',       'يوسف منصور',         'ماكاسار',   '2004-06-09', 'سولاويسي الجنوبية', 'ماكاسار',  'فقه',   'متن الغاية والتقريب'],
            ['Idris Al-Attas',     'إدريس العطاس',       'بالمبانج',   '2005-09-27', 'سومطرة الجنوبية', 'بالمبانج',   'نحو',   'متممة الأجرومية'],
        ];

        $dibuat = ['diterima' => 0, 'menunggu' => 0];
        $kataSandi = Hash::make('percobaan123');
        $kelasIds = Kelas::query()->orderBy('id')->pluck('id')->all(); // dibagi rata ke kelas yang ada (bila ada)
        $pendidikan = ['K', 'L', 'M', 'o', 'L'];

        foreach ($peserta as $i => [$nama, $arab, $tempat, $lahir, $provinsi, $kota, $fan, $kitab]) {
            $email = 'percobaan' . ($i + 1) . '@contoh.test';
            if (PendaftaranSiswa::where('email', $email)->exists()) {
                continue;
            }

            $diterima = $i < self::JUMLAH_DITERIMA;

            DB::transaction(function () use ($i, $nama, $arab, $tempat, $lahir, $provinsi, $kota, $fan, $kitab, $email, $kataSandi, $diterima, $kelasIds, $pendidikan) {
                // Daftar dulu sebagai "menunggu" (kode QR dibuat otomatis oleh model)
                $p = PendaftaranSiswa::create([
                    'nomor_pendaftaran' => PendaftaranSiswa::buatNomor(),
                    'nama_lengkap'      => $nama,
                    'nama_lengkap_arab' => $arab,
                    'tempat_lahir'      => $tempat,
                    'tanggal_lahir'     => $lahir,
                    'status_pernikahan' => 'L',
                    'no_hp'             => sprintf('+6281234567%02d', $i + 1),
                    'provinsi'          => $provinsi,
                    'kota'              => $kota,
                    'kecamatan'         => 'المركز',
                    'kelurahan'         => 'القرية',
                    'alamat'            => 'شارع السلام رقم ' . ($i + 1),
                    'nama_wali'         => 'والد ' . $arab,
                    'pekerjaan_wali'    => 'تاجر',
                    'hubungan_wali'     => 'أب',
                    'no_hp_wali'        => sprintf('+6281345678%02d', $i + 1),
                    'riwayat_pondok'    => [[
                        'asal_pondok'   => 'معهد دار السلام',
                        'nama_pengasuh' => 'الشيخ عبد الرحمن',
                        'nama_daerah'   => $kota,
                        'tahun_belajar' => 2 + $i % 4,
                    ]],
                    'riwayat_kitab'     => [[
                        'nama_kitab'     => $kitab,
                        'fan_kitab'      => $fan,
                        'sudah_diujikan' => $i % 2 ? 'sudah' : 'belum',
                    ]],
                    'pendidikan_terakhir' => $pendidikan[$i % count($pendidikan)],
                    'nama_sekolah'      => 'المدرسة الثانوية',
                    'nama_daerah'       => $kota,
                    'marhalah'          => 'ثانوية',
                    'lama_belajar'      => (2 + $i % 4) . ' سنوات',
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

                // Akun login dibuat seperti saat mendaftar sungguhan
                $p->buatAkun();

                if ($diterima) {
                    // Sama seperti tombol "Terima" di admin (tanpa kirim WhatsApp)
                    $p->terima('Diterima lewat data percobaan.');
                    if ($kelasIds) {
                        $p->forceFill(['kelas_id' => $kelasIds[$i % count($kelasIds)]])->save();
                    }
                }
            });

            $dibuat[$diterima ? 'diterima' : 'menunggu']++;
        }

        $this->command?->info("Pendaftar PSB percobaan dibuat: {$dibuat['diterima']} diterima, {$dibuat['menunggu']} menunggu (yang sudah ada dilewati).");
    }
}
