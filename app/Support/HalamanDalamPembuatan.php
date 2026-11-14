<?php

namespace App\Support;

use Illuminate\Contracts\View\View;

/**
 * Menu admin yang halamannya BELUM dibuat -> satu halaman umum "Dalam masa pembuatan"
 * (resources/views/admin/dalam-pembuatan.blade.php). Rutenya dibuat otomatis di routes/web.php (grup admin).
 *
 * Nama, kelompok & ikon diambil dari MenuAdmin, jadi tidak ditulis dua kali.
 * Kalau halaman aslinya sudah jadi: HAPUS barisnya dari DAFTAR, lalu buat rute aslinya dengan nama yang sama.
 */
class HalamanDalamPembuatan
{
    /** Nama rute (tanpa "admin.") => alamat (setelah /admin/) */
    public const DAFTAR = [
        'biaya-pendidikan.index'  => 'keuangan/biaya-pendidikan',
        'pembayaran.index'        => 'keuangan/pembayaran',
        'laporan-akademik.index'  => 'laporan-akademik',
        'laporan-keuangan.index'  => 'laporan-keuangan',
        'landing-fitur.index'     => 'landing/fitur-unggulan',
        'landing-kurikulum.index' => 'landing/kurikulum',
    ];

    /** Halaman umum untuk satu menu (judul, kelompok & ikon dari MenuAdmin) */
    public static function tampil(string $nama): View
    {
        $rute = 'admin.' . $nama;
        $menu = ['judul' => $nama, 'kelompok' => null, 'ikon' => 'hammer'];

        foreach (MenuAdmin::semua() as $grup) {
            foreach ($grup['submenu'] ?? [] as $sub) {
                if ($sub['route'] === $rute) {
                    $menu = ['judul' => $sub['label'], 'kelompok' => $grup['label'] ?? null, 'ikon' => $sub['icon'] ?? 'hammer'];
                    break 2;
                }
            }
        }

        return view('admin.dalam-pembuatan', $menu);
    }
}
