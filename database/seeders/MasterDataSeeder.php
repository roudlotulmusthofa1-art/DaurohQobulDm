<?php

namespace Database\Seeders;

use App\Models\BidangIlmu;
use App\Models\JenisKegiatan;
use App\Models\JenisKhidmah;
use App\Models\JenisPelanggaran;
use App\Models\Kamar;
use App\Models\KategoriBerita;
use App\Models\Kelas;
use App\Models\WaktuPelajaran;
use Illuminate\Database\Seeder;

/**
 * Data contoh Master Data: 5 data untuk setiap menu.
 *
 * Jalankan:  php artisan db:seed --class=MasterDataSeeder
 *
 * Aman dijalankan berkali-kali: data dengan nama yang sama diperbarui, tidak dobel.
 * Sengaja TIDAK dipanggil dari DatabaseSeeder, supaya tidak ikut jalan saat deploy.
 */
class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->isi(BidangIlmu::class, [
            ['nama' => 'Nahwu',  'warna' => 'bg-blue-100',   'deskripsi' => 'Ilmu tata bahasa Arab.'],
            ['nama' => 'Shorof', 'warna' => 'bg-indigo-100', 'deskripsi' => 'Ilmu perubahan bentuk kata.'],
            ['nama' => 'Fiqih',  'warna' => 'bg-green-100',  'deskripsi' => 'Ilmu hukum-hukum syariat.'],
            ['nama' => 'Tauhid', 'warna' => 'bg-amber-100',  'deskripsi' => 'Ilmu akidah.'],
            ['nama' => 'Tajwid', 'warna' => 'bg-rose-100',   'deskripsi' => 'Ilmu membaca Al-Qur\'an.'],
        ]);

        $this->isi(WaktuPelajaran::class, [
            ['nama' => "Ba'da Subuh",   'jam_mulai' => '05:00', 'jam_selesai' => '06:30'],
            ['nama' => 'Pagi',          'jam_mulai' => '08:00', 'jam_selesai' => '10:00'],
            ['nama' => "Ba'da Ashar",   'jam_mulai' => '15:30', 'jam_selesai' => '17:00'],
            ['nama' => "Ba'da Maghrib", 'jam_mulai' => '18:15', 'jam_selesai' => '19:00'],
            ['nama' => "Ba'da Isya",    'jam_mulai' => '19:30', 'jam_selesai' => '21:00'],
        ]);

        $this->isi(Kelas::class, [
            ['nama' => "I'dadiyah", 'kode' => 'IDD'],
            ['nama' => 'Ula 1',     'kode' => 'U1'],
            ['nama' => 'Ula 2',     'kode' => 'U2'],
            ['nama' => 'Wustho',    'kode' => 'WST'],
            ['nama' => 'Ulya',      'kode' => 'ULY'],
        ]);

        $this->isi(Kamar::class, [
            ['nama' => 'Kamar Abu Bakar', 'kode' => 'A-01', 'kapasitas' => 12, 'kasur_per_baris' => 4],
            ['nama' => 'Kamar Umar',      'kode' => 'A-02', 'kapasitas' => 12, 'kasur_per_baris' => 4],
            ['nama' => 'Kamar Utsman',    'kode' => 'A-03', 'kapasitas' => 10, 'kasur_per_baris' => 5],
            ['nama' => 'Kamar Ali',       'kode' => 'A-04', 'kapasitas' => 10, 'kasur_per_baris' => 5],
            ['nama' => 'Kamar Hamzah',    'kode' => 'A-05', 'kapasitas' => 8,  'kasur_per_baris' => 4],
        ]);

        $this->isi(JenisKegiatan::class, [
            ['nama' => 'Muhadharah',      'warna' => 'bg-teal-100',   'deskripsi' => 'Latihan ceramah santri.'],
            ['nama' => "Ro'an",           'warna' => 'bg-lime-100',   'deskripsi' => 'Kerja bakti bersama.'],
            ['nama' => 'Ziarah',          'warna' => 'bg-violet-100', 'deskripsi' => 'Ziarah makam auliya.'],
            ['nama' => 'Maulid',          'warna' => 'bg-pink-100',   'deskripsi' => 'Pembacaan maulid bersama.'],
            ['nama' => 'Sholat Berjamaah','warna' => 'bg-sky-100',    'deskripsi' => 'Sholat lima waktu berjamaah.'],
        ]);

        $this->isi(JenisPelanggaran::class, [
            ['nama' => 'Terlambat sholat berjamaah', 'warna' => 'bg-amber-100',  'deskripsi' => 'Datang setelah iqamah.'],
            ['nama' => 'Tidak ikut pelajaran',       'warna' => 'bg-orange-100', 'deskripsi' => 'Absen tanpa izin.'],
            ['nama' => 'Keluar pondok tanpa izin',   'warna' => 'bg-red-100',    'deskripsi' => 'Meninggalkan pondok tanpa izin pengurus.'],
            ['nama' => 'Membawa HP',                 'warna' => 'bg-rose-100',   'deskripsi' => 'Menyimpan HP di kamar.'],
            ['nama' => 'Tidur saat pelajaran',       'warna' => 'bg-yellow-100', 'deskripsi' => 'Tidur ketika pelajaran berlangsung.'],
        ]);

        $this->isi(JenisKhidmah::class, [
            ['nama' => 'Kebersihan Masjid', 'warna' => 'bg-sky-100',     'deskripsi' => 'Menyapu dan merapikan masjid.'],
            ['nama' => 'Dapur',             'warna' => 'bg-orange-100',  'deskripsi' => 'Membantu memasak dan mencuci alat dapur.'],
            ['nama' => 'Keamanan',          'warna' => 'bg-indigo-100',  'deskripsi' => 'Jaga malam dan gerbang.'],
            ['nama' => 'Perpustakaan',      'warna' => 'bg-emerald-100', 'deskripsi' => 'Merapikan kitab dan mencatat peminjaman.'],
            ['nama' => 'Kebersihan Kamar',  'warna' => 'bg-cyan-100',    'deskripsi' => 'Piket kebersihan kamar dan halaman.'],
        ]);

        $this->isi(KategoriBerita::class, [
            ['nama' => 'Kegiatan',   'warna' => 'bg-green-100'],
            ['nama' => 'Pengumuman', 'warna' => 'bg-amber-100'],
            ['nama' => 'Kajian',     'warna' => 'bg-blue-100'],
            ['nama' => 'Prestasi',   'warna' => 'bg-violet-100'],
            ['nama' => 'Alumni',     'warna' => 'bg-pink-100'],
        ]);

        $this->command?->info('Master Data contoh: 8 menu × 5 data.');
    }

    /** Simpan / perbarui berdasarkan nama; urutan mengikuti urutan array. */
    private function isi(string $model, array $baris): void
    {
        foreach ($baris as $i => $data) {
            $model::updateOrCreate(
                ['nama' => $data['nama']],
                $data + ['urutan' => $i + 1, 'is_active' => true],
            );
        }
    }
}
