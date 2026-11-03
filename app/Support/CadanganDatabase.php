<?php

namespace App\Support;

use App\Models\Sesi;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Backup, restore, dan hapus data untuk database sesi aktif.
 * File backup otomatis disimpan di storage/app/cadangan/{kode sesi}/ (tidak bisa dibuka dari web).
 */
class CadanganDatabase
{
    /** Tabel sementara (login, cache, antrian) tidak ikut di-backup / di-restore */
    public const TABEL_SEMENTARA = ['sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs'];

    /** Tabel yang hanya dipakai di database utama (daftar sesi) */
    public const TABEL_PUSAT = ['sesi'];

    /**
     * Kelompok "Hapus data". Urutan tabel = urutan penghapusan.
     * Akun admin & asatid tidak pernah ikut terhapus (dipakai bersama semua sesi).
     */
    public const KELOMPOK = [
        'peserta' => [
            'label' => 'Peserta PSB & Santri',
            'keterangan' => 'Data pendaftar, santri, akun login peserta, dan berkas upload peserta. Absensi peserta ikut terhapus.',
            'tabel' => ['pendaftaran_siswas'],
            'folder' => ['psb'],
        ],
        'absensi' => [
            'label' => 'Absensi',
            'keterangan' => 'Semua absensi kelas & kegiatan beserta rinciannya.',
            'tabel' => ['absensi_detail', 'absensi'],
        ],
        'akademik' => [
            'label' => 'Akademik',
            'keterangan' => 'Tahun & bulan ajaran, kurikulum & kitab, jadwal mengajar.',
            'tabel' => ['jadwal_mengajar', 'kitab', 'bulan_ajaran', 'tahun_ajaran'],
        ],
        'keuangan' => [
            'label' => 'Buku Kas',
            'keterangan' => 'Semua transaksi kas masuk & keluar beserta file buktinya. Kategori tetap disimpan.',
            'tabel' => ['kas_transaksi'],
            'folder' => ['buku-kas'],
        ],
        'master' => [
            'label' => 'Master Data',
            'keterangan' => 'Bidang ilmu, waktu pelajaran, kelas, kamar, jenis kegiatan/pelanggaran/pencapaian/khidmah, kategori berita.',
            'tabel' => ['bidang_ilmu', 'waktu_pelajaran', 'kelas', 'kamar', 'jenis_kegiatan', 'jenis_pelanggaran', 'jenis_pencapaian', 'jenis_khidmah', 'kategori_berita'],
        ],
        'pengaturan' => [
            'label' => 'Pengaturan',
            'keterangan' => 'Profil pesantren (logo, nama, alamat) dan pengaturan notifikasi sesi ini.',
            'tabel' => ['pengaturan'],
            'folder' => ['pengaturan'],
        ],
    ];

    public static function folder(Sesi $sesi): string
    {
        return storage_path('app/cadangan/' . $sesi->kode);
    }

    /** Tabel milik database (tanpa tabel sementara & tabel pusat) */
    public static function daftarTabel(string $koneksi): array
    {
        return collect(Schema::connection($koneksi)->getTables(DB::connection($koneksi)->getDatabaseName()))
            ->pluck('name')->reject(fn ($t) => in_array($t, [...self::TABEL_SEMENTARA, ...self::TABEL_PUSAT], true))
            ->values()->all();
    }

    /** Tulis isi backup (.sql) ke $tulis (fungsi yang menerima potongan teks) */
    public static function tulis(string $koneksi, callable $tulis): void
    {
        $db = DB::connection($koneksi);
        $pdo = $db->getPdo();
        $tulis('-- Backup database ' . $db->getDatabaseName() . ' · ' . now()->toDateTimeString() . "\n");
        $tulis("-- Dauroh Qobul. Pulihkan lewat Pengaturan > Database > Restore.\n");
        $tulis("SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n");

        foreach (self::daftarTabel($koneksi) as $nama) {
            $buat = (array) $db->selectOne("SHOW CREATE TABLE `{$nama}`");
            $tulis("DROP TABLE IF EXISTS `{$nama}`;\n" . end($buat) . ";\n\n");

            $db->table($nama)->orderByRaw('1')->chunk(500, function ($baris) use ($nama, $pdo, $tulis) {
                $nilai = $baris->map(fn ($b) => '(' . collect((array) $b)
                    ->map(fn ($v) => $v === null ? 'NULL' : $pdo->quote((string) $v))->implode(',') . ')');
                $tulis("INSERT INTO `{$nama}` VALUES\n" . $nilai->implode(",\n") . ";\n");
            });
            $tulis("\n");
        }

        $tulis("SET FOREIGN_KEY_CHECKS=1;\n");
    }

    /** Simpan backup sesi ke file di server. $alasan masuk ke nama file, mis. "manual", "sebelum-restore". */
    public static function simpan(Sesi $sesi, string $alasan = 'manual'): string
    {
        File::ensureDirectoryExists(self::folder($sesi));
        $path = self::folder($sesi) . '/' . now()->format('Y-m-d_His') . '_' . $alasan . '.sql';
        $file = fopen($path, 'w');
        self::tulis($sesi->koneksi(), fn ($teks) => fwrite($file, $teks));
        fclose($file);

        return basename($path);
    }

    /** Daftar file backup sesi (terbaru dulu) */
    public static function daftar(Sesi $sesi): array
    {
        if (! is_dir(self::folder($sesi))) {
            return [];
        }

        return collect(File::files(self::folder($sesi)))
            ->filter(fn ($f) => $f->getExtension() === 'sql')
            ->map(fn ($f) => ['nama' => $f->getFilename(), 'ukuran' => $f->getSize(), 'waktu' => \Carbon\Carbon::createFromTimestamp($f->getMTime())])
            ->sortByDesc('waktu')->values()->all();
    }

    /** Path aman file backup (nama file saja, tanpa folder lain) */
    public static function pathFile(Sesi $sesi, string $nama): string
    {
        $nama = basename($nama);
        $path = self::folder($sesi) . '/' . $nama;
        if (! preg_match('/^[\w.-]+\.sql$/', $nama) || ! is_file($path)) {
            throw new RuntimeException('File backup tidak ditemukan.');
        }

        return $path;
    }

    /**
     * Pulihkan database sesi dari file .sql. Backup otomatis dibuat dulu.
     * Setelah itu tabel diperbarui (migrasi) dan akun bersama disamakan lagi.
     */
    public static function pulihkan(Sesi $sesi, string $pathSql): string
    {
        $isi = file_get_contents($pathSql);
        if ($isi === false || trim($isi) === '') {
            throw new RuntimeException('File backup kosong atau tidak bisa dibaca.');
        }
        if (preg_match('/^\s*(DROP\s+DATABASE|CREATE\s+DATABASE|USE\s+`?\w|GRANT|CREATE\s+USER|DROP\s+USER)/im', $isi)) {
            throw new RuntimeException('File berisi perintah yang tidak diizinkan (membuat/menghapus/memilih database atau user).');
        }

        $cadangan = self::simpan($sesi, 'sebelum-restore');
        $koneksi = $sesi->koneksi();

        DB::connection($koneksi)->unprepared($isi);
        DB::connection($koneksi)->unprepared('SET FOREIGN_KEY_CHECKS=1');

        self::rapikanSetelahUbah($sesi);

        return $cadangan;
    }

    /** Hapus data per kelompok di sesi ini. Backup otomatis dibuat dulu. */
    public static function hapusKelompok(Sesi $sesi, array $kelompok): string
    {
        $cadangan = self::simpan($sesi, 'sebelum-hapus-data');
        $db = DB::connection($sesi->koneksi());
        $ada = self::daftarTabel($sesi->koneksi());

        $db->transaction(function () use ($db, $kelompok, $ada) {
            foreach ($kelompok as $k) {
                foreach (self::KELOMPOK[$k]['tabel'] as $tabel) {
                    if (in_array($tabel, $ada, true)) {
                        $db->table($tabel)->delete(); // DELETE (bukan TRUNCATE) agar aturan relasi tetap berlaku
                    }
                }
                if ($k === 'peserta') {
                    $db->table('users')->where('role', 'siswa')->delete();
                }
            }
        });

        // Berkas upload milik kelompok itu (folder file sesi aktif)
        foreach ($kelompok as $k) {
            foreach (self::KELOMPOK[$k]['folder'] ?? [] as $folder) {
                Storage::disk('public')->deleteDirectory($folder);
            }
        }

        return $cadangan;
    }

    /** Setelah restore: perbarui tabel ke versi aplikasi & samakan akun admin/asatid */
    private static function rapikanSetelahUbah(Sesi $sesi): void
    {
        $koneksi = $sesi->koneksi();

        if (! $sesi->utama) {
            // migrasi + nomor id peserta per sesi + samakan akun admin/asatid
            PengelolaSesi::siapkan($sesi);

            return;
        }

        Artisan::call('migrate', ['--database' => $koneksi, '--force' => true]);
        if ($sesi->utama) {
            // Database utama = sumber akun bersama -> sebarkan ke semua sesi
            foreach (array_keys(SesiAplikasi::semuaKoneksi()) as $k) {
                AkunBersama::samakan($k);
            }
        } else {
            AkunBersama::samakan($koneksi);
        }
    }
}
