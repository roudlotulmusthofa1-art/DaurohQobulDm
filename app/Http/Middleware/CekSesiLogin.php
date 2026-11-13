<?php

namespace App\Http\Middleware;

use App\Support\AkunBersama;
use App\Support\MasukSebagai;
use App\Support\SesiAplikasi;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Login dicatat bersama sesi aktif saat itu.
 * Bila admin memindah sesi aktif: akun admin/asatid (akun bersama) tetap login,
 * akun peserta dikeluarkan karena datanya milik sesi lama.
 */
class CekSesiLogin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Akun dinonaktifkan admin saat sedang masuk -> langsung dikeluarkan
        // (kecuali Super Admin sedang "Masuk sebagai" akun itu, hanya untuk melihat)
        if (Auth::check() && Auth::user()->aktif === false && ! MasukSebagai::aktif() && $request->hasSession()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', __('Akun Anda dinonaktifkan. Hubungi admin.'));
        }

        $sesi = SesiAplikasi::aktif();

        if ($sesi && Auth::check() && $request->hasSession()) {
            $tercatat = $request->session()->get('sesi_login');

            if ($tercatat !== null && (int) $tercatat !== $sesi->id && ! AkunBersama::bersama(Auth::user()->role)) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('error', 'Sesi dauroh sudah berganti. Silakan masuk kembali.');
            }

            $request->session()->put('sesi_login', $sesi->id);
        }

        return $next($request);
    }
}
