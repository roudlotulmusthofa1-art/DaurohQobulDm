<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\MasukSebagai;
use Illuminate\Http\Request;

/**
 * Super Admin: "Masuk sebagai" asatid / siswa (hanya untuk melihat dasbor mereka).
 * Tombolnya ada di Detail Asatid, Detail Santri, dan Sistem > Daftar User.
 */
class MasukSebagaiController extends Controller
{
    /** POST /admin/masuk-sebagai/{user} */
    public function mulai(Request $request, User $user)
    {
        abort_unless(MasukSebagai::bolehDipakai($request->user()), 403, 'Hanya Super Admin yang bisa memakai fitur Masuk Sebagai.');

        if (! MasukSebagai::bolehDituju($user)) {
            return back()->with('error', 'Hanya akun asatid dan peserta yang bisa dilihat dengan Masuk Sebagai.');
        }

        MasukSebagai::mulai($request->user(), $user, url()->previous() ?: route('admin.dashboard'));

        return redirect($user->halamanAwal());
    }

    /** POST /masuk-sebagai/mode — tombol "Izinkan perubahan" / "Kembali ke mode lihat" di pita atas */
    public function mode(Request $request)
    {
        $nyala = $request->boolean('ubah');
        MasukSebagai::aturUbah($nyala);

        return back()->with('success', $nyala
            ? 'Perubahan diizinkan: data yang Anda simpan tercatat atas nama ' . $request->user()->name . '.'
            : 'Kembali ke mode lihat: perubahan tidak disimpan.');
    }

    /** POST /masuk-sebagai/selesai — tombol "Kembali ke Admin" di pita atas */
    public function selesai()
    {
        $kembali = MasukSebagai::selesai();

        return $kembali
            ? redirect($kembali)->with('success', 'Anda kembali ke akun admin.')
            : redirect()->route('login');
    }
}
