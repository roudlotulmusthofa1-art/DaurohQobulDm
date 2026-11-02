<?php

namespace App\Providers;

use App\Support\NotifikasiAdmin;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.admin', function ($view) {
            $menuItems = [
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
                    'icon'  => 'users',
                    'submenu' => [
                        ['route' => 'admin.ustadz.index',       'active' => 'admin.ustadz.*',       'label' => 'Data Ustadz', 'icon' => 'presentation'],
                        ['route' => 'admin.kurikulum.index',    'active' => 'admin.kurikulum.*',    'label' => 'Kurikulum & Kitab', 'icon' => 'book-open'],
                        ['route' => 'admin.tahun-ajaran.index', 'active' => 'admin.tahun-ajaran.*', 'label' => 'Tahun Ajaran', 'icon' => 'calendar-range'],
                        ['route' => 'admin.jadwal.index',       'active' => 'admin.jadwal.*',       'label' => 'Jadwal Pelajaran', 'icon' => 'calendar-clock'],
                    ],
                ],

                [
                    'label' => 'KEUANGAN',
                    'icon'  => 'wallet',
                    'submenu' => [
                        ['route' => 'admin.biaya-pendidikan.index', 'active' => 'admin.biaya-pendidikan.*', 'label' => 'Biaya Pendidikan', 'icon' => 'receipt'],
                        ['route' => 'admin.pembayaran.index',       'active' => 'admin.pembayaran.*',       'label' => 'Pembayaran Santri', 'icon' => 'credit-card'],
                        ['route' => 'admin.tagihan.index',          'active' => 'admin.tagihan.*',          'label' => 'Tagihan', 'icon' => 'file-text'],
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
                    ],
                ],
                [
                    'label' => 'LANDING PAGE',
                    'icon'  => 'globe',
                    'submenu' => [
                        ['route' => 'admin.landing-hero.index',      'active' => 'admin.landing-hero.*',      'label' => 'Hero & Statistik', 'icon' => 'layout-template'],
                        ['route' => 'admin.landing-fitur.index',     'active' => 'admin.landing-fitur.*',     'label' => 'Fitur Unggulan', 'icon' => 'sparkles'],
                        ['route' => 'admin.landing-kurikulum.index', 'active' => 'admin.landing-kurikulum.*', 'label' => 'Kurikulum Terintegrasi', 'icon' => 'layers'],
                    ],
                ],

                [
                    'label' => 'SISTEM',
                    'icon'  => 'settings',
                    'submenu' => [
                        ['route' => 'admin.akun.index', 'active' => 'admin.akun.*', 'label' => 'Kelola Akun', 'icon' => 'user-cog'],
                        ['route' => 'admin.role.index', 'active' => 'admin.role.*', 'label' => 'Role & Permission', 'icon' => 'shield-check'],
                        ['route' => 'admin.master-data.index', 'active' => 'admin.master-data.*', 'label' => 'Master Data', 'icon' => 'database'],
                        ['route' => 'admin.pengaturan.index', 'active' => 'admin.pengaturan.*', 'label' => 'Pengaturan', 'icon' => 'sliders-horizontal'],
                    ],
                ],
            ];

            $menuItemsForVue = collect($menuItems)->map(function ($item) {
                if (!empty($item['route'])) {
                    $item['url']    = Route::has($item['route']) ? route($item['route']) : '#';
                    $item['active'] = request()->routeIs($item['active']);
                } else {
                    $item['url']    = '#';
                    $item['active'] = false;
                }

                if (!empty($item['submenu'])) {
                    $item['submenu'] = collect($item['submenu'])->map(function ($sub) {
                        $sub['url']    = Route::has($sub['route']) ? route($sub['route']) : '#';
                        $sub['active'] = request()->routeIs($sub['active']);
                        return $sub;
                    })->all();
                }

                return $item;
            })->all();

            $initiallyOpenMenus = collect($menuItemsForVue)
                ->filter(fn($item) => !empty($item['submenu']) && collect($item['submenu'])->contains('active', true))
                ->pluck('label')
                ->all();

            // ===== Breadcrumb (Beranda > Halaman), dibuat otomatis dari menu di atas =====
            $breadcrumbs = [['label' => 'Beranda', 'url' => route('admin.dashboard')]];

            // Cari submenu yang sedang aktif
            $aktif = collect($menuItemsForVue)->pluck('submenu')->filter()->flatten(1)->firstWhere('active', true);

            if ($aktif) {
                $breadcrumbs[] = ['label' => $aktif['label'], 'url' => $aktif['url']];

                // Kalau bukan halaman daftarnya (mis. detail/edit), tambahkan satu langkah lagi
                $aksi = [
                    'show' => 'Detail',
                    'edit' => 'Edit',
                    'create' => 'Tambah',
                ][str(request()->route()?->getName())->afterLast('.')->toString()] ?? null;

                if ($aksi) {
                    $breadcrumbs[] = ['label' => $aksi, 'url' => null];
                }
            }

            // Langkah terakhir = halaman yang sedang dibuka, tidak perlu jadi link
            $breadcrumbs[array_key_last($breadcrumbs)]['url'] = null;

            // ===== Notifikasi (ikon lonceng di kanan atas) — jenisnya diatur di Sistem > Pengaturan =====
            ['items' => $notifItems, 'total' => $jumlahNotif] = NotifikasiAdmin::kumpulkan();

            $view->with(compact('menuItemsForVue', 'initiallyOpenMenus', 'breadcrumbs', 'jumlahNotif', 'notifItems'));
        });
    }
}
