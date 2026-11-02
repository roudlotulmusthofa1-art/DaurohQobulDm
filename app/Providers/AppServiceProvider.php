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
        // Koneksi "pusat" = database utama (daftar sesi, login, akun bersama). Lihat App\Support\SesiAplikasi
        \App\Support\SesiAplikasi::daftarkanKoneksiPusat();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // {{ $teks }} di Blade: escape biasa + "{{" di dalam data dipatahkan agar tidak dijalankan Vue
        \Illuminate\Support\Facades\Blade::setEchoFormat('\App\Support\TeksAman::e(%s)');

        // Permintaan web membaca database & folder file milik sesi aktif (perintah artisan tetap di database utama)
        if (! $this->app->runningInConsole()) {
            \App\Support\SesiAplikasi::terapkan();
        }

        View::composer('layouts.admin', function ($view) {
            // Daftar menu ada di App\Support\MenuAdmin; menu yang tidak diizinkan (Role & Permission) disembunyikan
            $menuItems = \App\Support\MenuAdmin::untukAkun(auth()->user());

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
