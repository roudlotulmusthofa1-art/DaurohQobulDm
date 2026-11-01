<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PendaftaranSiswa;
use App\Models\Pengaturan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Admin > Landing Page > Hero & Statistik
 * Mengatur angka statistik yang tampil di bagian atas beranda.
 */
class LandingHeroController extends Controller
{
    public function index(): View
    {
        return view('admin.landing-hero', [
            // Dihitung otomatis dari database (tidak bisa diubah manual)
            'jumlahPeserta' => PendaftaranSiswa::where('status', PendaftaranSiswa::DITERIMA)->count(),
            // Diisi admin
            'jumlahAssatid' => (int) Pengaturan::ambil('jumlah_assatid', 15),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate(
            ['jumlah_assatid' => ['required', 'integer', 'min:0', 'max:10000']],
            [
                'required' => ':attribute wajib diisi.',
                'integer'  => ':attribute harus berupa angka bulat.',
                'min'      => ':attribute tidak boleh kurang dari :min.',
                'max'      => ':attribute maksimal :max.',
            ],
            ['jumlah_assatid' => 'Jumlah assatid']
        );

        Pengaturan::simpan('jumlah_assatid', $data['jumlah_assatid']);

        return redirect()->route('admin.landing-hero.index')
            ->with('success', 'Statistik beranda berhasil disimpan.');
    }
}
