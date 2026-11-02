<?php

use App\Models\Sesi;
use App\Support\PengelolaSesi;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Perbarui tabel di database semua sesi (selain sesi utama) + samakan akun admin/asatid.
 * Jalankan setelah "php artisan migrate" (sudah otomatis di deploy.sh).
 */
Artisan::command('sesi:migrate', function () {
    if (! Schema::connection('pusat')->hasTable('sesi')) {
        $this->warn('Tabel sesi belum ada. Jalankan "php artisan migrate" dulu.');

        return 0;
    }

    $gagal = 0;
    foreach (Sesi::whereNotNull('database')->orderBy('id')->get() as $sesi) {
        try {
            PengelolaSesi::siapkan($sesi);
            $this->info("✓ {$sesi->nama} ({$sesi->database})");
        } catch (Throwable $e) {
            $gagal++;
            $this->error("✗ {$sesi->nama} ({$sesi->database}): " . $e->getMessage());
        }
    }
    $this->line('Selesai.');

    return $gagal ? 1 : 0;
})->purpose('Migrasi database semua sesi dauroh & samakan akun admin/asatid');
