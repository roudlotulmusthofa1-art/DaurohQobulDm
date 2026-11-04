<?php

namespace App\Support;

use App\Models\PendaftaranAsatid;
use App\Models\PendaftaranSiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * Ganti password sendiri dari dasbor asatid / peserta (menu "Ganti Password").
 * Password baru disimpan di akun login (users) DAN di data pendaftaran, supaya tetap sama
 * bila admin nanti memproses ulang data pendaftaran (mis. menerima ulang peserta).
 * Akun asatid dipakai bersama semua sesi: password baru otomatis ikut tersalin (App\Support\AkunBersama).
 */
class GantiPasswordSendiri
{
    public static function simpan(Request $request, PendaftaranAsatid|PendaftaranSiswa|null $pendaftaran): void
    {
        $request->validate([
            'password_lama' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8), 'different:password_lama'],
        ], [
            'password_lama.required' => __('Masukkan password lama.'),
            'password_lama.current_password' => __('Password lama salah.'),
            'password.required' => __('Masukkan password baru.'),
            'password.confirmed' => __('Ulangi password baru belum sama.'),
            'password.min' => __('Password baru minimal 8 karakter.'),
            'password.different' => __('Password baru harus berbeda dari password lama.'),
        ]);

        $hash = Hash::make($request->input('password'));
        $user = $request->user();

        DB::transaction(function () use ($user, $pendaftaran, $hash) {
            $user->forceFill(['password' => $hash])->save();
            $pendaftaran?->forceFill(['password' => $hash])->save();
        });
    }
}
