<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Route Master Data di file sendiri
            Route::middleware('web')->group(base_path('routes/masterdata.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        // Cookie posisi sidebar admin ditulis oleh Vue (JavaScript), jadi tidak dienkripsi
        $middleware->encryptCookies(except: ['sidebar_tertutup']);
        // Sudah login lalu membuka /login -> diarahkan ke halaman sesuai role (admin / asatid / siswa)
        $middleware->redirectUsersTo(fn (Request $request) => $request->user()?->halamanAwal() ?? '/admin/beranda');
        // Peserta dikeluarkan bila sesi aktif berganti; admin & asatid tetap login
        // CekIzinAdmin: akun admin ber-role pengurus hanya membuka menu yang diizinkan (Sistem > Role & Permission)
        // JagaMasukSebagai: saat Super Admin "Masuk sebagai" asatid/siswa, mode hanya-lihat (tidak bisa menyimpan)
        $middleware->web(append: [\App\Http\Middleware\PilihBahasa::class, \App\Http\Middleware\CekSesiLogin::class, \App\Http\Middleware\CekIzinAdmin::class, \App\Http\Middleware\JagaMasukSebagai::class]);
        $middleware->alias([
            'peran' => \App\Http\Middleware\PeranMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            // Balas JSON untuk API dan untuk permintaan fetch/AJAX yang meminta JSON (Accept: application/json)
            fn (Request $request) => $request->is('api/*', 'admin/master-data/api/*') || $request->expectsJson(),
        );
    })->create();
