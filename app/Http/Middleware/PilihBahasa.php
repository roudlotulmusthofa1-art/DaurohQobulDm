<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Bahasa tampilan: Indonesia (bawaan) atau Arab, dipilih lewat tombol bahasa (cookie "bahasa").
 * Yang berubah hanya tulisan di tampilan (__('...') + lang/ar.json); route, database & parameter tetap.
 */
class PilihBahasa
{
    public const BAHASA = ['id' => 'Indonesia', 'ar' => 'العربية'];

    public function handle(Request $request, Closure $next)
    {
        $kode = $request->cookie('bahasa');
        app()->setLocale(array_key_exists((string) $kode, self::BAHASA) ? $kode : 'id');

        return $next($request);
    }
}
