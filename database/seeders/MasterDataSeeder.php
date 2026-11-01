<?php

namespace Database\Seeders;

use App\Models\BidangIlmu;
use App\Models\Kamar;
use App\Models\KategoriBerita;
use App\Models\Kelas;
use App\Models\WaktuPelajaran;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seed(BidangIlmu::class, 'slug', [
            ['nama' => 'Nahwu',          'slug' => 'nahwu',         'warna' => 'bg-blue-100'],
            ['nama' => 'Shorof',         'slug' => 'shorof',        'warna' => 'bg-indigo-100'],
            ['nama' => 'Fiqh',           'slug' => 'fiqh',          'warna' => 'bg-green-100'],
            ['nama' => 'Ushul Fiqh',     'slug' => 'ushul_fiqh',    'warna' => 'bg-violet-100'],
            ['nama' => 'Tajwid',         'slug' => 'tajwid',        'warna' => 'bg-amber-100'],
            ['nama' => 'Manthiq',        'slug' => 'manthiq',       'warna' => 'bg-orange-100'],
            ['nama' => 'Tatbiq/Tadrib',  'slug' => 'tatbiqtadrib',  'warna' => 'bg-blue-100'],
            ['nama' => 'Ekstra',         'slug' => 'ekstra',        'warna' => 'bg-red-100'],
            ['nama' => 'Tashawuf/Rouha', 'slug' => 'tashawufrouha', 'warna' => 'bg-blue-100'],
        ]);

        $this->seed(WaktuPelajaran::class, 'nama', [
            ['nama' => "Ba'da Subuh",   'jam_mulai' => '05:00', 'jam_selesai' => '06:30'],
            ['nama' => 'Pagi',          'jam_mulai' => '08:00', 'jam_selesai' => '11:30'],
            ['nama' => "Ba'da Ashar",   'jam_mulai' => '15:30', 'jam_selesai' => '17:00'],
            ['nama' => "Ba'da Maghrib", 'jam_mulai' => '18:15', 'jam_selesai' => '19:00'],
            ['nama' => "Ba'da Isya",    'jam_mulai' => '19:30', 'jam_selesai' => '21:00'],
        ]);

        $this->seed(Kelas::class, 'kode', [
            ['nama' => "I'dadiyah", 'kode' => 'IDD', 'tingkat' => 1],
            ['nama' => 'Ula 1',     'kode' => 'U1',  'tingkat' => 2],
            ['nama' => 'Ula 2',     'kode' => 'U2',  'tingkat' => 3],
            ['nama' => 'Wustho 1',  'kode' => 'W1',  'tingkat' => 4],
            ['nama' => 'Wustho 2',  'kode' => 'W2',  'tingkat' => 5],
            ['nama' => 'Ulya',      'kode' => 'ULY', 'tingkat' => 6],
        ]);

        $this->seed(Kamar::class, 'kode', [
            ['nama' => 'Kamar Abu Bakar', 'kode' => 'A-01', 'gedung' => 'Gedung A', 'jenis' => 'putra', 'kapasitas' => 12],
            ['nama' => 'Kamar Umar',      'kode' => 'A-02', 'gedung' => 'Gedung A', 'jenis' => 'putra', 'kapasitas' => 12],
            ['nama' => 'Kamar Utsman',    'kode' => 'A-03', 'gedung' => 'Gedung A', 'jenis' => 'putra', 'kapasitas' => 10],
            ['nama' => 'Kamar Ali',       'kode' => 'A-04', 'gedung' => 'Gedung A', 'jenis' => 'putra', 'kapasitas' => 10],
        ]);

        $this->seed(KategoriBerita::class, 'slug', [
            ['nama' => 'Kegiatan',   'slug' => 'kegiatan',   'warna' => 'bg-green-100'],
            ['nama' => 'Pengumuman', 'slug' => 'pengumuman', 'warna' => 'bg-amber-100'],
            ['nama' => 'Kajian',     'slug' => 'kajian',     'warna' => 'bg-blue-100'],
            ['nama' => 'Prestasi',   'slug' => 'prestasi',   'warna' => 'bg-violet-100'],
        ]);
    }

    /** Insert / update berdasarkan kolom unik; urutan mengikuti urutan array. */
    private function seed(string $model, string $uniqueBy, array $rows): void
    {
        foreach ($rows as $i => $row) {
            $model::updateOrCreate(
                [$uniqueBy => $row[$uniqueBy]],
                $row + ['urutan' => $i + 1, 'is_active' => true]
            );
        }
    }
}
