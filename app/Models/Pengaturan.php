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

    /** Simpan (buat baru atau ubah) nilai pengaturan. */
    public static function simpan(string $kunci, mixed $nilai): void
    {
        static::updateOrCreate(['kunci' => $kunci], ['nilai' => $nilai]);
    }
}
