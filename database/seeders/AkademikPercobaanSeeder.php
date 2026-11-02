<?php

namespace Database\Seeders;

use App\Models\BidangIlmu;
use App\Models\BulanAjaran;
use App\Models\JadwalMengajar;
use App\Models\Kelas;
use App\Models\Kitab;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Models\WaktuPelajaran;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Data percobaan menu Akademik: 3 tahun ajaran (masing-masing 3 bulan), 3 kitab, 3 jadwal.
 *
 * Jalankan:  php artisan db:seed --class=AkademikPercobaanSeeder
 *
 * - Butuh Master Data (Kelas, Waktu Pelajaran, Bidang Ilmu). Jalankan MasterDataSeeder dulu bila masih kosong.
 * - Ustadz di jadwal diambil dari akun asatid yang sudah ada (kalau belum ada, ustadz dikosongkan).
 * - Aman dijalankan berkali-kali (data dengan nama yang sama tidak dibuat dobel).
 * - Tidak dipanggil dari DatabaseSeeder dan tidak berjalan di server production.
 */
class AkademikPercobaanSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->warn('Dilewati: data percobaan tidak boleh masuk ke server production.');

            return;
        }

        DB::transaction(function () {
            // ----- 3 tahun ajaran, masing-masing 3 bulan (tanggal mengikuti awal bulan Hijriyah) -----
            $tahunData = [
                ['1447/1448', '2025/2026', false, [
                    ["Rabi'ul Akhir 1447", '2025-09-23', '2025-10-22'],
                    ['Jumadil Ula 1447',   '2025-10-23', '2025-11-21'],
                    ['Jumadil Akhirah 1447', '2025-11-22', '2025-12-21'],
                ]],
                ['1448/1449', '2026/2027', true, [
                    ["Rabi'ul Akhir 1448", '2026-09-12', '2026-10-11'],
                    ['Jumadil Ula 1448',   '2026-10-12', '2026-11-10'],
                    ['Jumadil Akhirah 1448', '2026-11-11', '2026-12-09'],
                ]],
                ['1449/1450', '2027/2028', false, [
                    ["Rabi'ul Akhir 1449", '2027-09-01', '2027-09-30'],
                    ['Jumadil Ula 1449',   '2027-10-01', '2027-10-30'],
                    ['Jumadil Akhirah 1449', '2027-10-31', '2027-11-29'],
                ]],
            ];

            $bulanAktif = null;
            foreach ($tahunData as [$nama, $masehi, $aktif, $bulanList]) {
                $tahun = TahunAjaran::firstOrCreate(['nama' => $nama], [
                    'nama_masehi' => $masehi,
                    'tanggal_mulai' => $bulanList[0][1],
                    'tanggal_selesai' => end($bulanList)[2],
                    'keterangan' => 'Data percobaan',
                ]);
                foreach ($bulanList as $i => [$namaBulan, $mulai, $selesai]) {
                    $bulan = BulanAjaran::firstOrCreate(
                        ['tahun_ajaran_id' => $tahun->id, 'nama' => $namaBulan],
                        ['urutan' => $i + 1, 'tanggal_mulai' => $mulai, 'tanggal_selesai' => $selesai],
                    );
                    // Bulan aktif: bulan di tahun aktif yang memuat tanggal hari ini (atau bulan pertamanya)
                    if ($aktif && (! $bulanAktif || now()->between($mulai, $selesai . ' 23:59:59'))) {
                        $bulanAktif = $bulan;
                    }
                }
            }
            $bulanAktif?->aktifkan();

            // ----- 3 kitab -----
            $bidang = fn (string $nama) => BidangIlmu::where('nama', 'like', $nama . '%')->value('id');
            $kelas = Kelas::ordered()->pluck('id');

            $kitab = collect([
                ['Al-Ajurrumiyyah', 'الآجرومية', 'Ibnu Ajurrum', 'Nahwu', 'teori', [1, 2], 3],
                ['Safinatun Naja', 'سفينة النجا', 'Syekh Salim bin Sumair', 'Fiq', 'teori', [1, 2, 3], 2],
                ["Tuhfatul Athfal", 'تحفة الأطفال', 'Syekh Sulaiman al-Jamzuri', 'Tajwid', 'praktik', [], 2],
            ])->map(function ($k, $i) use ($bidang, $kelas) {
                [$nama, $arab, $pengarang, $namaBidang, $jenis, $bulanKe, $frekuensi] = $k;
                $kitab = Kitab::firstOrCreate(['nama' => $nama], [
                    'nama_arab' => $arab, 'pengarang' => $pengarang, 'bidang_ilmu_id' => $bidang($namaBidang),
                    'jenis' => $jenis, 'bulan_ke' => $bulanKe, 'frekuensi' => $frekuensi,
                    'deskripsi' => 'Data percobaan',
                ]);
                // Kitab ke-1 di 2 kelas pertama, lainnya di 1 kelas
                $kitab->kelas()->syncWithoutDetaching($kelas->slice($i === 0 ? 0 : $i - 1, $i === 0 ? 2 : 1)->values());

                return $kitab;
            });

            // ----- 3 jadwal di bulan aktif -----
            $waktu = WaktuPelajaran::ordered()->where('is_active', true)->pluck('id');
            $ustadz = User::where('role', 'asatid')->orderBy('id')->pluck('id');

            if ($bulanAktif && $waktu->isNotEmpty() && $kelas->isNotEmpty()) {
                foreach ([[1, 0, 0], [3, 0, 1], [6, 1, 2]] as $n => [$hari, $iWaktu, $iKitab]) {
                    $k = $kitab[$iKitab];
                    JadwalMengajar::firstOrCreate([
                        'bulan_ajaran_id' => $bulanAktif->id,
                        'hari' => $hari,
                        'waktu_pelajaran_id' => $waktu[$iWaktu] ?? $waktu->first(),
                        'kelas_id' => $k->kelas()->value('kelas.id') ?? $kelas->first(),
                    ], [
                        'kitab_id' => $k->id,
                        'user_id' => $ustadz->isNotEmpty() ? $ustadz[$n % $ustadz->count()] : null,
                    ]);
                }
            }
        });

        $this->command?->info('Akademik percobaan: 3 tahun ajaran (3 bulan per tahun), 3 kitab, 3 jadwal.');
    }
}
