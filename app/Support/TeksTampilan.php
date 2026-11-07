<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

/**
 * Daftar tulisan tampilan per halaman untuk Sistem > Pengaturan > Bahasa.
 *
 * Cara kerja: semua file tampilan (resources/views) dan file PHP (app/) dipindai,
 * lalu setiap tulisan yang dibungkus __('...') atau @lang('...') dikumpulkan.
 * Tulisan yang BELUM dibungkus tidak muncul (dan tidak bisa diterjemahkan).
 *
 * Mengelompokkan file ke "halaman": ubah daftar HALAMAN di bawah.
 * Aturan dibaca dari atas; file masuk ke aturan PERTAMA yang awalannya cocok.
 */
class TeksTampilan
{
    /** kode halaman => [nama halaman, awalan path file (relatif dari folder proyek)] */
    public const HALAMAN = [
        'admin-menu'      => ['Admin — Menu sidebar & header', ['resources/views/layouts/admin', 'app/Support/MenuAdmin.php', 'app/Providers/AppServiceProvider.php']],
        'admin-dasbor'    => ['Admin — Dasbor', ['resources/views/admin/dashboard', 'resources/views/admin/partials/pergantian-pelajaran']],
        'admin-penilaian' => ['Admin — Penilaian', ['resources/views/admin/penilaian/', 'app/Http/Controllers/Admin/PenilaianController.php', 'app/Http/Controllers/Admin/PergantianPelajaranController.php']],
        'admin-santri'    => ['Admin — Data Santri & Pendaftaran', ['resources/views/admin/datasantri', 'resources/views/admin/pendaftaran/', 'resources/views/admin/santri-kartu-qr', 'resources/views/partials/detail-santri', 'resources/views/partials/rincian-peserta']],
        'admin-asatid'    => ['Admin — Data Asatid', ['resources/views/admin/asatid/']],
        'admin-admin'     => ['Admin — Data Admin', ['resources/views/admin/data-admin/']],
        'admin-akademik'  => ['Admin — Akademik', ['resources/views/admin/akademik/']],
        'admin-keuangan'  => ['Admin — Keuangan', ['resources/views/admin/keuangan/']],
        'admin-absensi'   => ['Admin — Laporan Absensi', ['resources/views/admin/laporan-absensi/']],
        'admin-master'    => ['Admin — Master Data', ['resources/views/admin/master-data/', 'resources/views/components/master-data/', 'app/Http/Controllers/Admin/MasterDataController.php']],
        'admin-pesan'     => ['Admin — Pesan Peserta', ['resources/views/admin/pesan-peserta/']],
        'admin-beranda'   => ['Admin — Beranda & Pita Pengumuman', ['resources/views/admin/landing-', 'resources/views/admin/_pita-pratinjau']],
        'admin-sistem'    => ['Admin — Sistem (User, Role)', ['resources/views/admin/sistem/']],
        'admin-atur'      => ['Admin — Pengaturan', ['resources/views/admin/pengaturan/']],
        'admin-lain'      => ['Admin — Lainnya', ['resources/views/admin/']],
        'daftar'          => ['Formulir Pendaftaran', ['resources/views/psb/', 'resources/views/pendaftaran-admin/', 'resources/views/asatid/daftar', 'resources/views/asatid/sukses']],
        'asatid-kerangka' => ['Asatid — Header & kerangka', ['resources/views/layouts/asatid', 'resources/views/asatid/partials/pengingat-pelajaran']],
        'asatid-dasbor'   => ['Asatid — Dasbor', ['resources/views/asatid/dashboard']],
        'asatid-absensi'  => ['Asatid — Absensi', ['resources/views/asatid/absensi/']],
        'asatid-penilaian' => ['Asatid — Penilaian', ['resources/views/asatid/penilaian/', 'app/Http/Controllers/Asatid/PenilaianController.php']],
        'asatid-lain'     => ['Asatid — Halaman lain (jadwal, peserta, penilaian, profil, pesan)', ['resources/views/asatid/']],
        'siswa-kerangka'  => ['Peserta — Header & kerangka', ['resources/views/layouts/siswa']],
        'siswa-dasbor'    => ['Peserta — Dasbor', ['resources/views/siswa/dashboard']],
        'siswa-lain'      => ['Peserta — Halaman lain (profil, kelulusan, pesan)', ['resources/views/siswa/']],
        'login'           => ['Halaman Login', ['resources/views/auth/']],
        'beranda'         => ['Beranda publik', ['resources/views/landing', 'resources/views/layouts/app', 'resources/views/partials/navbar', 'resources/views/partials/footer', 'resources/views/partials/floating-widgets']],
        'bersama'         => ['Bagian bersama (lonceng, pita, tombol, dialog)', ['resources/views/']],
        'sistem'          => ['Pesan dari sistem (notifikasi, peringatan, dll.)', ['app/']],
    ];

    /**
     * Semua halaman beserta tulisannya:
     * kode => ['nama' => ..., 'teks' => ['Tulisan Indonesia' => ['file1', 'file2']]]
     */
    public static function semua(): array
    {
        $hasil = collect(self::HALAMAN)->map(fn ($h) => ['nama' => $h[0], 'teks' => []])->all();
        $dasar = base_path() . DIRECTORY_SEPARATOR;

        $file = array_merge(File::allFiles(resource_path('views')), File::allFiles(app_path()));

        foreach ($file as $f) {
            if ($f->getExtension() !== 'php') {
                continue;
            }

            $path = str_replace('\\', '/', substr($f->getPathname(), strlen($dasar)));
            $kode = self::halamanUntuk($path);
            if (! $kode) {
                continue;
            }

            $namaPendek = preg_replace(['#^resources/views/#', '#\.blade\.php$#', '#\.php$#'], '', $path);
            foreach (self::cariTeks(File::get($f->getPathname())) as $teks) {
                $hasil[$kode]['teks'][$teks][] = $namaPendek;
            }
        }

        // Label yang disimpan di konstanta (tampil lewat __($variabel), jadi tidak terbaca pemindai di atas)
        foreach (self::dariKode() as $kode => $daftar) {
            foreach ($daftar as $sumber => $semuaTeks) {
                foreach ($semuaTeks as $teks) {
                    if (is_string($teks) && trim($teks) !== '') {
                        $hasil[$kode]['teks'][$teks][] = $sumber;
                    }
                }
            }
        }

        foreach ($hasil as &$h) {
            $h['teks'] = array_map(fn ($daftar) => array_values(array_unique($daftar)), $h['teks']);
        }

        return $hasil;
    }

    /**
     * Tulisan dari konstanta PHP yang ditampilkan dengan __($variabel).
     * kode halaman => ['sumber (nama konstanta)' => [daftar tulisan]]
     * Menambah konstanta baru: tulis di sini + bungkus tempat tampilnya dengan __(...).
     */
    public static function dariKode(): array
    {
        $statusAbsensi = array_column(\App\Models\Absensi::STATUS, 0);
        $hari = array_values(\App\Models\JadwalMengajar::HARI);
        $aspekNilai = array_column(\App\Models\NilaiHarian::ASPEK, 0); // 6 penilaian Nilai Harian
        $siswa = \App\Models\PendaftaranSiswa::class;
        $dataPeserta = [
            'PendaftaranSiswa::SYARAT' => array_values($siswa::SYARAT),
            'PendaftaranSiswa::BERKAS' => array_values($siswa::BERKAS),
            'PendaftaranSiswa::STATUS_PERNIKAHAN' => array_values($siswa::STATUS_PERNIKAHAN),
            'PendaftaranSiswa::PENDIDIKAN_TERAKHIR' => array_values($siswa::PENDIDIKAN_TERAKHIR),
            'PendaftaranSiswa::PERNAH_DAUROH' => array_values($siswa::PERNAH_DAUROH),
            'status pendaftaran' => ['Diterima', 'Ditolak', 'Menunggu Verifikasi'],
        ];

        return [
            'admin-santri'   => $dataPeserta,
            'siswa-lain'     => $dataPeserta,
            'admin-absensi'  => ['Absensi::STATUS' => $statusAbsensi],
            'asatid-absensi' => ['Absensi::STATUS' => $statusAbsensi],
            'asatid-lain'    => ['Absensi::STATUS' => $statusAbsensi, 'JadwalMengajar::HARI' => $hari],
            'admin-akademik' => ['JadwalMengajar::HARI' => $hari],
            'admin-penilaian'  => ['NilaiHarian::ASPEK' => $aspekNilai],
            'asatid-penilaian' => ['NilaiHarian::ASPEK' => $aspekNilai],
            'admin-atur'     => [
                'PengaturanController::TAB' => array_column(\App\Http\Controllers\Admin\PengaturanController::TAB, 'label'),
                'NotifikasiAdmin::JENIS' => array_merge(...array_map(fn ($j) => [$j['judul'], $j['keterangan']], array_values(NotifikasiAdmin::JENIS))),
                'PesanWa::GRUP' => array_values(PesanWa::GRUP),
                'PesanWa::ISIAN' => array_values(PesanWa::ISIAN),
            ],
        ];
    }

    /** Kode halaman untuk sebuah file (aturan pertama yang cocok). */
    public static function halamanUntuk(string $path): ?string
    {
        foreach (self::HALAMAN as $kode => [, $awalan]) {
            foreach ($awalan as $a) {
                if (str_starts_with($path, $a)) {
                    return $kode;
                }
            }
        }

        return null;
    }

    /** Ambil semua tulisan di dalam __('...'), __("..."), @lang('...') dan trans('...') dari isi sebuah file. */
    public static function cariTeks(string $isi): array
    {
        preg_match_all('/(?<![\w$>:])(?:__|@lang|trans)\(\s*(?:\'((?:\\\\.|[^\'\\\\])*)\'|"((?:\\\\.|[^"\\\\])*)")\s*[,)]/s', $isi, $cocok, PREG_SET_ORDER);

        $teks = [];
        foreach ($cocok as $c) {
            if (isset($c[2]) && $c[2] !== '') {
                if (str_contains($c[2], '$')) {
                    continue; // berisi variabel PHP, bukan tulisan tetap
                }
                $t = stripcslashes($c[2]);
            } else {
                $t = str_replace(["\\'", '\\\\'], ["'", '\\'], $c[1]);
            }

            if (trim($t) !== '' && $t !== '...') { // '...' = contoh di komentar kode
                $teks[] = $t;
            }
        }

        return array_values(array_unique($teks));
    }
}
