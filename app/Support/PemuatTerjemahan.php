<?php

namespace App\Support;

use App\Models\Terjemahan;
use Illuminate\Contracts\Translation\Loader;

/**
 * Pemuat terjemahan: membungkus pemuat bawaan Laravel.
 * Tulisan Arab __('...') HANYA diambil dari Sistem > Pengaturan > Bahasa (tabel "terjemahan"), diisi admin sendiri.
 * File lang/ar.json sengaja TIDAK dipakai. Tulisan yang belum diisi tetap tampil dalam bahasa Indonesia.
 *
 * Dipasang di AppServiceProvider::register() lewat $this->app->extend('translation.loader', ...).
 */
class PemuatTerjemahan implements Loader
{
    public function __construct(private Loader $asli)
    {
    }

    public function load($locale, $group, $namespace = null)
    {
        // Tulisan __('...') = grup "*" (JSON). Bahasa Arab: hanya isian admin dari database.
        if ($locale === 'ar' && $group === '*' && $namespace === '*') {
            return Terjemahan::semua();
        }

        return $this->asli->load($locale, $group, $namespace);
    }

    public function addNamespace($namespace, $hint)
    {
        $this->asli->addNamespace($namespace, $hint);
    }

    public function addJsonPath($path)
    {
        $this->asli->addJsonPath($path);
    }

    public function namespaces()
    {
        return $this->asli->namespaces();
    }
}
