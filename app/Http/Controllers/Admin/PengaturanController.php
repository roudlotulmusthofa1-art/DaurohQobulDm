<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengaturan;
use App\Support\NotifikasiAdmin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Admin > Sistem > Pengaturan
 * Saat ini berisi: Pengaturan Notifikasi (jenis notifikasi apa saja yang muncul di lonceng).
 */
class PengaturanController extends Controller
{
    public function index(): View
    {
        $notifikasi = collect(NotifikasiAdmin::JENIS)->map(fn ($j, $kunci) => $j + [
            'kunci' => $kunci,
            'aktif' => NotifikasiAdmin::aktif($kunci),
            'ikon_path' => NotifikasiAdmin::IKON[$j['ikon']],
            'warna_kelas' => NotifikasiAdmin::WARNA[$j['warna']],
        ]);

        return view('admin.pengaturan', compact('notifikasi'));
    }

    public function update(Request $request): RedirectResponse
    {
        // Kotak yang tidak dicentang tidak terkirim -> dianggap mati
        $dipilih = (array) $request->input('notif', []);

        foreach (array_keys(NotifikasiAdmin::JENIS) as $kunci) {
            Pengaturan::simpan('notif_' . $kunci, ! empty($dipilih[$kunci]) ? '1' : '0');
        }

        return redirect()->route('admin.pengaturan.index')
            ->with('success', 'Pengaturan notifikasi berhasil disimpan.');
    }
}
