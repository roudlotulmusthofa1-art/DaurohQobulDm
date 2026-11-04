<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Reset password akun login asatid / admin (tombol "Reset Password" di halaman detail Data Asatid / Data Admin).
 * Password baru diketik admin sendiri. Dipakai oleh PendaftaranAsatid dan PendaftaranAdmin
 * (keduanya punya kolom user_id, email, password).
 * Akun admin & asatid dipakai bersama semua sesi: saat akun disimpan, password baru otomatis disalin
 * ke database utama dan semua sesi (lihat App\Support\AkunBersama).
 */
trait ResetPasswordAkun
{
    /** Akun login yang terhubung (null bila belum diterima / akun sudah dihapus) */
    public function akunLogin(): ?User
    {
        if (! $this->user_id) {
            return null;
        }

        return User::find($this->user_id) ?? User::on('pusat')->find($this->user_id);
    }

    /**
     * Simpan password baru (diketik admin) untuk akun login ini. Password lama langsung tidak berlaku.
     * @return bool  false bila akun login tidak ditemukan
     */
    public function resetPassword(string $baru): bool
    {
        $user = $this->akunLogin();
        if (! $user) {
            return false;
        }

        $hash = Hash::make($baru);

        DB::connection('pusat')->transaction(function () use ($user, $hash) {
            $this->forceFill(['password' => $hash])->save();   // data pendaftaran
            $user->forceFill(['password' => $hash])->save();   // akun login (ikut tersalin ke semua sesi)
        });

        return true;
    }
}
