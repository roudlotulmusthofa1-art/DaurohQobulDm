<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Pengaturan sederhana (kunci => nilai) yang bisa diubah admin.
 *
 * Contoh:
 *   Pengaturan::ambil('notif_pendaftar_baru', '1');
 *   Pengaturan::simpan('notif_pendaftar_baru', '0');
 */
class Pengaturan extends Model
{
    protected $table = 'pengaturan';

    protected $fillable = ['kunci', 'nilai'];

    /** Ambil nilai pengaturan; kalau belum ada, pakai $bawaan. */
    public static function ambil(string $kunci, mixed $bawaan = null): mixed
    {
        return static::where('kunci', $kunci)->value('nilai') ?? $bawaan;
    }

    /**
     * Profil pesantren (Sistem > Pengaturan > Umum), dipakai di sidebar dan halaman lain.
     * Kunci: pesantren_nama, pesantren_alamat, pesantren_telepon, pesantren_email, pesantren_logo (path di disk public).
     */
    public static function profilPesantren(): array
    {
        static $profil = null;

        if ($profil === null) {
            $nilai = static::where('kunci', 'like', 'pesantren\_%')->pluck('nilai', 'kunci');
            $logo = $nilai['pesantren_logo'] ?? null;
            $profil = [
                'nama' => $nilai['pesantren_nama'] ?? 'Dauroh Qobul',
                'alamat' => $nilai['pesantren_alamat'] ?? null,
                'telepon' => $nilai['pesantren_telepon'] ?? null,
                'email' => $nilai['pesantren_email'] ?? null,
                'logo' => $logo,
                'logo_url' => $logo ? \Illuminate\Support\Facades\Storage::disk('public')->url($logo) : asset('images/Logo dar4.png'),
            ];
        }

        return $profil;
    }

    /** Pengaturan yang berlaku untuk SEMUA sesi (disimpan di database utama), mis. kode link pendaftaran asatid/admin. */
    public static function ambilPusat(string $kunci, mixed $bawaan = null): mixed
    {
        return static::on('pusat')->where('kunci', $kunci)->value('nilai') ?? $bawaan;
    }

    public static function simpanPusat(string $kunci, mixed $nilai): void
    {
        static::on('pusat')->updateOrCreate(['kunci' => $kunci], ['nilai' => $nilai]);
    }

    /** Simpan (buat baru atau ubah) nilai pengaturan. */
    public static function simpan(string $kunci, mixed $nilai): void
    {
        static::updateOrCreate(['kunci' => $kunci], ['nilai' => $nilai]);
    }
}
