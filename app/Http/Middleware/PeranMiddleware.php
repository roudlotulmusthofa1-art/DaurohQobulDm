<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Batasi halaman berdasarkan role, contoh di route: ->middleware('peran:admin')
 *
 * Kalau yang membuka bukan role yang sesuai (mis. akun asatid membuka halaman admin),
 * ia diarahkan ke halamannya sendiri dengan pesan, bukan halaman 403 kosong.
 */
class PeranMiddleware
{
    /** Nama yang tampil di pesan */
    private const NAMA_PERAN = [
        'admin'  => 'admin',
        'asatid' => 'asatid',
        'siswa'  => 'siswa',
    ];

    public function handle(Request $request, Closure $next, string $peran)
    {
        $user = $request->user();

        if ($user?->role === $peran) {
            return $next($request);
        }

        // Permintaan data (fetch/JSON) atau akun tanpa role yang dikenal: tetap 403
        if (! $user || $request->expectsJson() || ! isset(self::NAMA_PERAN[$user->role])) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $tujuan = $user->halamanAwal();

        // Jaga-jaga supaya tidak berputar-putar ke halaman yang sama
        if (rtrim($request->url(), '/') === rtrim($tujuan, '/')) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return redirect($tujuan)->with('error',
            'Halaman tadi khusus ' . (self::NAMA_PERAN[$peran] ?? $peran) . '. '
            . 'Anda sedang masuk sebagai ' . self::NAMA_PERAN[$user->role] . ' (' . $user->email . '). '
            . 'Keluar dulu lalu masuk dengan akun yang sesuai.');
    }
}
