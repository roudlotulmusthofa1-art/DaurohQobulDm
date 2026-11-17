<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AktivitasPengguna;
use Illuminate\Http\Request;

/**
 * Sistem > Aktivitas Pengguna (admin/aktivitas?peran=asatid|siswa&cari=):
 * semua catatan AktivitasPengguna (masuk/keluar + simpan/ubah/hapus oleh asatid & siswa), terbaru dulu.
 */
class AktivitasPenggunaController extends Controller
{
    public function index(Request $request)
    {
        $peran = in_array($request->query('peran'), AktivitasPengguna::PERAN, true) ? $request->query('peran') : '';
        $cari = trim((string) $request->query('cari'));

        $dasar = fn () => AktivitasPengguna::query()
            ->when($cari !== '', fn ($q) => $q->where(fn ($w) => $w->where('nama', 'like', "%{$cari}%")->orWhere('aksi', 'like', "%{$cari}%")));

        return view('admin.sistem.aktivitas', [
            'daftar' => $dasar()->when($peran, fn ($q) => $q->where('peran', $peran))->terbaru()->paginate(50)->withQueryString(),
            'peran'  => $peran,
            'cari'   => $cari,
            'jumlah' => [
                ''       => $dasar()->count(),
                'asatid' => $dasar()->where('peran', 'asatid')->count(),
                'siswa'  => $dasar()->where('peran', 'siswa')->count(),
            ],
        ]);
    }
}
