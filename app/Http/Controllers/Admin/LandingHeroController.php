<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PendaftaranAsatid;
use App\Models\PendaftaranSiswa;
use Illuminate\View\View;

/**
 * Admin > Landing Page > Hero & Statistik
 * Menampilkan angka statistik di bagian atas beranda.
 * Kedua angka dihitung otomatis dari database, jadi tidak ada yang perlu diisi manual.
 */
class LandingHeroController extends Controller
{
    public function index(): View
    {
        return view('admin.landing-hero', [
            'jumlahPeserta' => PendaftaranSiswa::where('status', PendaftaranSiswa::DITERIMA)->count(),
            'jumlahAssatid' => PendaftaranAsatid::where('status', PendaftaranAsatid::DITERIMA)->count(),
        ]);
    }
}
