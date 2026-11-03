<?php

namespace App\Support;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Daftar menu sidebar admin, di SATU tempat.
 * Dipakai oleh:
 * - sidebar (AppServiceProvider)            -> menu yang tidak diizinkan disembunyikan
 * - halaman Sistem > Role & Permission      -> daftar centang izin
 * - middleware CekIzinAdmin                 -> halaman yang tidak diizinkan tidak bisa dibuka
 *
 * Kunci izin = nama route submenu, mis. "admin.santri.index".
 */
class MenuAdmin
{
    /** Route yang selalu boleh dibuka semua admin (tidak muncul di daftar izin) */
    private const SELALU_BOLEH = ['admin.dashboard', 'admin.logout'];

    /** Menu khusus Super Admin (tidak bisa diberikan ke role lain) */
    private const KHUSUS_SUPER = ['admin.role.index'];

    public static function semua(): array
    {
        return [
            ['route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'label' => 'DASHBOARD', 'icon' => 'home'],

            [
                'label' => 'MANAJEMEN DATA',
                'icon'  => 'graduation-cap',
                'submenu' => [
                    ['route' => 'admin.santri.index', 'active' => 'admin.santri.*', 'label' => 'Data Santri', 'icon' => 'user-round'],
                    ['route' => 'admin.asatid.index',   'active' => 'admin.asatid.*',   'label' => 'Data Asatid', 'icon' => 'user-check'],
                    ['route' => 'admin.data-admin.index', 'active' => 'admin.data-admin.*', 'label' => 'Data Admin', 'icon' => 'user-cog'],
                ],
            ],

            [
                'label' => 'AKADEMIK',
                'icon'  => 'book-open-check',
                'submenu' => [
                    ['route' => 'admin.tahun-ajaran.index', 'active' => ['admin.tahun-ajaran.*', 'admin.bulan-ajaran.*'], 'label' => 'Tahun & Bulan Ajaran', 'icon' => 'calendar-range'],
                    ['route' => 'admin.kurikulum.index',    'active' => 'admin.kurikulum.*',    'label' => 'Kurikulum & Kitab', 'icon' => 'book-open'],
                    ['route' => 'admin.jadwal.index',       'active' => 'admin.jadwal.*',       'label' => 'Jadwal Mengajar', 'icon' => 'calendar-clock'],
                    ['route' => 'admin.kelulusan.index',    'active' => 'admin.kelulusan.*',    'label' => 'Kelulusan', 'icon' => 'award'],
                ],
            ],

            [
                'label' => 'KEUANGAN',
                'icon'  => 'wallet',
                'submenu' => [
                    ['route' => 'admin.biaya-pendidikan.index', 'active' => 'admin.biaya-pendidikan.*', 'label' => 'Biaya Pendidikan', 'icon' => 'receipt'],
                    ['route' => 'admin.pembayaran.index',       'active' => 'admin.pembayaran.*',       'label' => 'Pembayaran Santri', 'icon' => 'credit-card'],
                    ['route' => 'admin.buku-kas.index',        'active' => 'admin.buku-kas.*',        'label' => 'Buku Kas', 'icon' => 'book-open-text'],
                ],
            ],
            [
                'label' => 'LAPORAN',
                'icon'  => 'bell',
                'submenu' => [
                    ['route' => 'admin.laporan-akademik.index', 'active' => 'admin.laporan-akademik.*', 'label' => 'Laporan Akademik', 'icon' => 'chart-column'],
                    ['route' => 'admin.laporan-keuangan.index',  'active' => 'admin.laporan-keuangan.*',  'label' => 'Laporan Keuangan', 'icon' => 'chart-pie'],
                    ['route' => 'admin.laporan-absensi.index',   'active' => 'admin.laporan-absensi.*',   'label' => 'Laporan Absensi', 'icon' => 'clipboard-check'],
                ],
            ],
            [
                'label' => 'PSB',
                'icon'  => 'calendar',
                'submenu' => [
                    // admin.pendaftaran.* ikut dihitung, supaya halaman detail/edit peserta juga masuk menu ini
                    ['route' => 'admin.data-masuk.index',      'active' => ['admin.data-masuk.*', 'admin.pendaftaran.*'], 'label' => 'Data Masuk Peserta', 'icon' => 'user-plus'],
                    // Pesan dua arah dengan peserta (dasbor siswa)
                    ['route' => 'admin.pesan-peserta.index',   'active' => 'admin.pesan-peserta.*', 'label' => 'Pesan Peserta', 'icon' => 'messages-square'],
                ],
            ],
            [
                'label' => 'LANDING PAGE',
                'icon'  => 'globe',
                'submenu' => [
                    ['route' => 'admin.landing-pengumuman.index', 'active' => 'admin.landing-pengumuman.*', 'label' => 'Pita Pengumuman', 'icon' => 'megaphone'],
                    ['route' => 'admin.landing-hero.index',      'active' => 'admin.landing-hero.*',      'label' => 'Hero & Statistik', 'icon' => 'layout-template'],
                    ['route' => 'admin.landing-fitur.index',     'active' => 'admin.landing-fitur.*',     'label' => 'Fitur Unggulan', 'icon' => 'sparkles'],
                    ['route' => 'admin.landing-kurikulum.index', 'active' => 'admin.landing-kurikulum.*', 'label' => 'Kurikulum Terintegrasi', 'icon' => 'layers'],
                ],
            ],

            [
                'label' => 'SISTEM',
                'icon'  => 'settings',
                'submenu' => [
                    ['route' => 'admin.role.index', 'active' => 'admin.role.*', 'label' => 'Role & Permission', 'icon' => 'shield-check'],
                    ['route' => 'admin.akun.index', 'active' => 'admin.akun.*', 'label' => 'Daftar User', 'icon' => 'users'],
                    ['route' => 'admin.master-data.index', 'active' => 'admin.master-data.*', 'label' => 'Master Data', 'icon' => 'database'],
                    ['route' => 'admin.pengaturan.index', 'active' => 'admin.pengaturan.*', 'label' => 'Pengaturan', 'icon' => 'sliders-horizontal'],
                ],
            ],
        ];
    }

    /**
     * Daftar izin untuk halaman Role & Permission, dikelompokkan per menu:
     * [['label' => 'MANAJEMEN DATA', 'izin' => [['kunci' => 'admin.santri.index', 'label' => 'Data Santri'], ...]], ...]
     */
    public static function daftarIzin(): array
    {
        return collect(self::semua())
            ->filter(fn ($m) => ! empty($m['submenu']))
            ->map(fn ($m) => [
                'label' => $m['label'],
                'izin'  => collect($m['submenu'])
                    ->reject(fn ($s) => in_array($s['route'], self::KHUSUS_SUPER, true))
                    ->map(fn ($s) => ['kunci' => $s['route'], 'label' => $s['label'], 'icon' => $s['icon']])
                    ->values()->all(),
            ])
            ->filter(fn ($g) => $g['izin'])
            ->values()->all();
    }

    /** Semua kunci izin yang sah (untuk validasi) */
    public static function semuaKunci(): array
    {
        return collect(self::daftarIzin())->pluck('izin')->flatten(1)->pluck('kunci')->all();
    }

    /** Daftar kunci menu yang boleh dibuka akun ini. null = semua (Super Admin). */
    public static function izinAkun(?User $user): ?array
    {
        if (! $user || $user->role !== 'admin' || ! $user->role_id) {
            return null;
        }

        // Role sudah dihapus / tidak ditemukan: hanya dashboard (lebih aman daripada membuka semua)
        return Role::untukAkun($user)?->izin ?? [];
    }

    /** Apakah akun ini boleh membuka route dengan nama tsb? */
    public static function boleh(?User $user, ?string $namaRoute): bool
    {
        $izin = self::izinAkun($user);

        if ($izin === null || ! $namaRoute || in_array($namaRoute, self::SELALU_BOLEH, true)) {
            return true;
        }

        foreach (self::semua() as $menu) {
            foreach ($menu['submenu'] ?? [] as $sub) {
                if (Str::is((array) $sub['active'], $namaRoute)) {
                    return in_array($sub['route'], $izin, true) && ! in_array($sub['route'], self::KHUSUS_SUPER, true);
                }
            }
        }

        // Halaman yang tidak termasuk menu mana pun (mis. notifikasi): tetap boleh
        return true;
    }

    /** Menu sidebar yang sudah disaring sesuai izin akun */
    public static function untukAkun(?User $user): array
    {
        if (self::izinAkun($user) === null) {
            return self::semua();
        }

        return collect(self::semua())
            ->map(function ($menu) use ($user) {
                if (empty($menu['submenu'])) {
                    return $menu;
                }
                $menu['submenu'] = array_values(array_filter($menu['submenu'], fn ($s) => self::boleh($user, $s['route'])));

                return $menu['submenu'] ? $menu : null;
            })
            ->filter()->values()->all();
    }
}
