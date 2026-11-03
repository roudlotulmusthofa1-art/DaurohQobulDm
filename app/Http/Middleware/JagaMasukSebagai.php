<?php

namespace App\Http\Middleware;

use App\Support\MasukSebagai;
use Closure;
use Illuminate\Http\Request;

/**
 * Selama "Masuk sebagai" (Super Admin melihat dasbor asatid/siswa), bawaannya MODE LIHAT:
 * - semua permintaan menyimpan (POST/PUT/DELETE) ditolak, kecuali "Kembali ke Admin" & ganti mode;
 *   bila admin menekan "Izinkan perubahan", simpan diperbolehkan (atas nama akun yang dilihat);
 * - tombol Keluar di dasbor tersebut = kembali ke akun admin.
 * Dipasang di grup web (bootstrap/app.php).
 */
class JagaMasukSebagai
{
    public function handle(Request $request, Closure $next)
    {
        if (! MasukSebagai::aktif()) {
            return $next($request);
        }

        $nama = (string) $request->route()?->getName();

        if (in_array($nama, ['masuk-sebagai.selesai', 'masuk-sebagai.mode'], true)) {
            return $next($request);
        }

        // Tombol "Keluar" di dasbor asatid/siswa -> kembali ke akun admin (bukan logout)
        if (str_ends_with($nama, '.logout')) {
            return redirect()->to(route('masuk-sebagai.selesai'))->setStatusCode(307); // tetap POST
        }

        if (! in_array($request->method(), ['GET', 'HEAD'], true) && ! MasukSebagai::bolehUbah()) {
            $pesan = 'Mode lihat: perubahan tidak disimpan. Tekan "Izinkan perubahan" di pita kuning bila ingin menyimpan.';

            return $request->expectsJson()
                ? response()->json(['message' => $pesan], 403)
                : back()->with('error', $pesan);
        }

        return $next($request);
    }
}
