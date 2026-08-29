<?php

namespace App\Providers;

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
                        ['route' => 'admin.santri.index', 'active' => 'admin.santri.*', 'label' => 'Data Santri'],
                        ['route' => 'admin.asatid.index',   'active' => 'admin.asatid.*',   'label' => 'Data Asatid'],
                        ['route' => 'admin.kelas.index',  'active' => 'admin.kelas.*',  'label' => 'Data Kelas'],
                    ],
                ],

                [
                    'label' => 'AKADEMIK',
                    'icon'  => 'users',
                    'submenu' => [
                        ['route' => 'admin.ustadz.index',       'active' => 'admin.ustadz.*',       'label' => 'Data Ustadz'],
                        ['route' => 'admin.kurikulum.index',    'active' => 'admin.kurikulum.*',    'label' => 'Kurikulum & Kitab'],
                        ['route' => 'admin.tahun-ajaran.index', 'active' => 'admin.tahun-ajaran.*', 'label' => 'Tahun Ajaran'],
                        ['route' => 'admin.jadwal.index',       'active' => 'admin.jadwal.*',       'label' => 'Jadwal Pelajaran'],
                    ],
                ],

                [
                    'label' => 'KEUANGAN',
                    'icon'  => 'wallet',
                    'submenu' => [
                        ['route' => 'admin.biaya-pendidikan.index', 'active' => 'admin.biaya-pendidikan.*', 'label' => 'Biaya Pendidikan'],
                        ['route' => 'admin.pembayaran.index',       'active' => 'admin.pembayaran.*',       'label' => 'Pembayaran Santri'],
                        ['route' => 'admin.tagihan.index',          'active' => 'admin.tagihan.*',          'label' => 'Tagihan'],
                    ],
                ],

                [
                    'label' => 'LAPORAN',
                    'icon'  => 'bell',
                    'submenu' => [
                        ['route' => 'admin.laporan-akademik.index', 'active' => 'admin.laporan-akademik.*', 'label' => 'Laporan Akademik'],
                        ['route' => 'admin.laporan-keuangan.index',  'active' => 'admin.laporan-keuangan.*',  'label' => 'Laporan Keuangan'],
                    ],
                ],

                ['route' => 'admin.kalender.index', 'active' => 'admin.kalender.*', 'label' => 'PSb', 'icon' => 'calendar'],

                [
                    'label' => 'LANDING PAGE',
                    'icon'  => 'globe',
                    'submenu' => [
                        ['route' => 'admin.landing-hero.index',      'active' => 'admin.landing-hero.*',      'label' => 'Hero & Statistik'],
                        ['route' => 'admin.landing-fitur.index',     'active' => 'admin.landing-fitur.*',     'label' => 'Fitur Unggulan'],
                        ['route' => 'admin.landing-kurikulum.index', 'active' => 'admin.landing-kurikulum.*', 'label' => 'Kurikulum Terintegrasi'],
                    ],
                ],

                [
                    'label' => 'SISTEM',
                    'icon'  => 'settings',
                    'submenu' => [
                        ['route' => 'admin.akun.index', 'active' => 'admin.akun.*', 'label' => 'Kelola Akun'],
                        ['route' => 'admin.role.index', 'active' => 'admin.role.*', 'label' => 'Role & Permission'],
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

            $view->with(compact('menuItemsForVue', 'initiallyOpenMenus'));
        });
    }
}
