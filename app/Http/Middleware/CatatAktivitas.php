<?php

namespace App\Http\Middleware;

use App\Models\AktivitasPengguna;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Catat setiap simpan / ubah / hapus (POST, PUT, PATCH, DELETE) oleh akun asatid & siswa
 * ke AktivitasPengguna, supaya admin melihatnya di lonceng notifikasi.
 * Tidak dicatat: membuka halaman (GET), permintaan yang gagal / isian salah, keluar (dicatat lewat event Logout),
 * dan mode "Masuk sebagai" admin.
 */
class CatatAktivitas
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return $response;
        }

        $user = $request->user();
        $rute = $request->route()?->getName();
        if (! $user || ! in_array($user->role, AktivitasPengguna::PERAN, true) || str_ends_with((string) $rute, '.logout')) {
            return $response;
        }

        // Hanya yang berhasil: bukan error, dan bukan kembali ke formulir karena isian salah
        $gagal = $response->getStatusCode() >= 400
            || ($request->hasSession() && in_array('errors', (array) $request->session()->get('_flash.new', []), true));
        if (! $gagal) {
            AktivitasPengguna::catat($user, AktivitasPengguna::aksiDariRute($rute, $request->method()), $request->fullUrl());
        }

        return $response;
    }
}
