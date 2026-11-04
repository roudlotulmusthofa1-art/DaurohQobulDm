<?php

namespace App\Support;

use App\Models\PendaftaranAdmin;
use App\Models\PendaftaranAsatid;
use App\Services\WhatsAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

/**
 * Tombol "Reset Password" di halaman detail Data Asatid & Data Admin (dan detail santri memakai validasi()).
 * Admin mengetik password baru sendiri. Password itu dikirim otomatis ke WhatsApp (template di Pengaturan > Pesan),
 * lalu ditampilkan SEKALI ke admin (session 'password_baru') beserta tombol kirim lewat WhatsApp sendiri.
 * Kesalahan isian disimpan di error bag "resetPassword" (tampil di bawah kolom isian).
 *
 * Aturan:
 * - Reset akun ADMIN hanya oleh Super Admin (pengurus tidak boleh mengambil alih akun admin lain).
 * - Akun sendiri tidak bisa direset dari sini (pakai Pengaturan > Profil).
 */
class ResetPasswordAkun
{
    /** Alasan tombol tidak boleh dipakai akun yang sedang login (null = boleh) */
    public static function alasanTidakBoleh(PendaftaranAsatid|PendaftaranAdmin $akun): ?string
    {
        $saya = auth()->user();

        if (! $akun->user_id) {
            return __('Akun login belum dibuat (dibuat saat pendaftar diterima).');
        }
        if ($saya && (int) $akun->user_id === (int) $saya->id) {
            return __('Ini akun Anda sendiri. Ganti password lewat Pengaturan > Profil.');
        }
        if ($akun instanceof PendaftaranAdmin && MenuAdmin::izinAkun($saya) !== null) {
            return __('Reset password akun admin hanya bisa dilakukan Super Admin.');
        }

        return null;
    }

    /** Periksa password baru yang diketik admin (dipakai juga reset password santri). Mengembalikan password-nya. */
    public static function validasi(Request $request): string
    {
        return $request->validateWithBag('resetPassword', [
            'password_baru' => ['required', 'string', 'confirmed', Password::min(8), 'max:100'],
        ], [
            'password_baru.required' => __('Ketik password baru.'),
            'password_baru.confirmed' => __('Ulangi password baru belum sama.'),
            'password_baru.min' => __('Password baru minimal 8 karakter.'),
        ])['password_baru'];
    }

    public static function jalankan(Request $request, PendaftaranAsatid|PendaftaranAdmin $akun, WhatsAppService $wa): RedirectResponse
    {
        if ($alasan = self::alasanTidakBoleh($akun)) {
            return back()->with('error', $alasan);
        }

        $baru = self::validasi($request);
        if (! $akun->resetPassword($baru)) {
            return back()->with('error', __('Akun login tidak ditemukan. Mungkin sudah dihapus.'));
        }

        $kunci = $akun instanceof PendaftaranAdmin ? 'admin_reset_password' : 'asatid_reset_password';
        $isian = ['nama' => $akun->nama_lengkap, 'email' => $akun->email, 'password' => $baru];
        // Pesan untuk tombol "kirim lewat WhatsApp saya" tetap dibuat walau pengiriman otomatis dimatikan
        $pesan = PesanWa::isi(PesanWa::teks($kunci), $isian);
        $terkirim = PesanWa::aktif($kunci) ? $wa->kirim($akun->no_hp, $pesan) : null;

        return back()
            // Password tampil sekali saja (flash), tidak disimpan dalam bentuk yang bisa dibaca
            ->with('password_baru', ['password' => $baru, 'pesan' => $pesan])
            ->with(...match ($terkirim) {
                true => ['success', __('Password baru disimpan dan sudah dikirim otomatis ke WhatsApp :nama.', ['nama' => $akun->nama_lengkap])],
                false => ['error', __('Password baru disimpan, tetapi chat otomatis GAGAL: :alasan. Kirim lewat tombol WhatsApp di bawah.', ['alasan' => $wa->alasan])],
                null => ['success', __('Password baru disimpan. Kirim lewat tombol WhatsApp di bawah.')],
            });
    }
}
