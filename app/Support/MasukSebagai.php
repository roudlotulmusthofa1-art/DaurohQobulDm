<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * "Masuk sebagai": Super Admin membuka dasbor asatid / siswa tertentu (data asli mereka) tanpa password,
 * untuk melihat tampilan & perubahan. Bawaannya MODE LIHAT (dijaga middleware JagaMasukSebagai):
 * tidak bisa menyimpan apa pun, dan membuka pesan tidak menandainya "sudah dibaca".
 * Admin bisa menekan "Izinkan perubahan" di pita atas -> bisa mengisi absensi dll. ATAS NAMA akun tersebut.
 * Kembali ke akun admin lewat pita di atas dasbor ("Kembali ke Admin").
 */
class MasukSebagai
{
    private const KUNCI = 'masuk_sebagai';

    /** Role yang boleh dilihat */
    public const PERAN_TUJUAN = ['asatid', 'siswa'];

    /** Sedang dalam mode "masuk sebagai"? */
    public static function aktif(): bool
    {
        return session()->has(self::KUNCI . '.admin_id');
    }

    /** Sedang dalam mode "izinkan perubahan" (boleh menyimpan atas nama akun yang dilihat)? */
    public static function bolehUbah(): bool
    {
        return self::aktif() && (bool) session(self::KUNCI . '.ubah', false);
    }

    /** Nyalakan / matikan mode izinkan perubahan */
    public static function aturUbah(bool $nyala): void
    {
        if (self::aktif()) {
            session()->put(self::KUNCI . '.ubah', $nyala);
        }
    }

    /** Akun admin asli (yang sedang menyamar) */
    public static function admin(): ?User
    {
        $id = session(self::KUNCI . '.admin_id');

        return $id ? User::find($id) : null;
    }

    /** Hanya Super Admin (admin tanpa role pengurus) yang boleh memakai fitur ini */
    public static function bolehDipakai(?User $user): bool
    {
        return $user?->role === 'admin' && MenuAdmin::izinAkun($user) === null && ! self::aktif();
    }

    public static function bolehDituju(?User $tujuan): bool
    {
        return $tujuan && in_array($tujuan->role, self::PERAN_TUJUAN, true);
    }

    /** Mulai: login sebagai $tujuan, catat admin asli & halaman untuk kembali */
    public static function mulai(User $admin, User $tujuan, string $kembaliKe): void
    {
        Auth::login($tujuan);
        session()->put(self::KUNCI, ['admin_id' => $admin->id, 'kembali' => $kembaliKe, 'ubah' => false]); // mulai dalam mode lihat
    }

    /** Selesai: login lagi sebagai admin asli. Mengembalikan URL halaman admin tadi. */
    public static function selesai(): ?string
    {
        $data = session(self::KUNCI);
        $admin = self::admin();
        session()->forget(self::KUNCI);

        if (! $admin) {
            return null;
        }

        Auth::login($admin);

        return $data['kembali'] ?? route('admin.dashboard');
    }
}
