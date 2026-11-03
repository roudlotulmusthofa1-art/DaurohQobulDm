<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Status "sudah dibaca" & "dihapus" untuk notifikasi lonceng (admin, asatid, siswa).
 *
 * Tiap notifikasi punya 'kunci' unik (mis. "pendaftar_baru:12") dan 'bobot'
 * (berapa angka di lonceng yang diwakilinya, bawaan 1).
 * - Dibaca  : angka merah berkurang, notifikasi tetap tampil (lebih pudar).
 * - Dihapus : notifikasi disembunyikan. Bila datanya berubah (kuncinya baru), muncul lagi.
 */
class StatusNotifikasi
{
    public const TABEL = 'notifikasi_status';

    /**
     * Terapkan status milik akun yang login ke daftar notifikasi.
     *
     * @return array{items: array<int, array>, belum: int}  belum = angka merah di lonceng
     */
    public static function terapkan(array $items): array
    {
        $status = collect();
        $kunci = array_values(array_filter(array_column($items, 'kunci')));
        if (Auth::id() && $kunci) {
            try {
                $status = DB::table(self::TABEL)->where('user_id', Auth::id())
                    ->whereIn('kunci', $kunci)->get()->keyBy('kunci');
            } catch (\Throwable $e) {
                $status = collect(); // tabel belum dimigrasi: semua dianggap baru
            }
        }

        $hasil = [];
        $belum = 0;
        foreach ($items as $n) {
            $s = $status[$n['kunci'] ?? ''] ?? null;
            if ($s?->dihapus_at) {
                continue;
            }
            $n['bobot'] = (int) ($n['bobot'] ?? 1);
            $n['dibaca'] = (bool) $s?->dibaca_at;
            // Keterangan kecil di bawah teks (admin: keterangan · waktu; asatid/siswa: ket)
            $n['ket'] = $n['ket'] ?? collect([$n['keterangan'] ?? null, $n['waktu'] ?? null])->filter()->implode(' · ');
            if (! $n['dibaca']) {
                $belum += $n['bobot'];
            }
            $hasil[] = $n;
        }

        return ['items' => $hasil, 'belum' => $belum];
    }

    /** Simpan status (aksi: baca | hapus) untuk beberapa kunci milik akun yang login. */
    public static function simpan(int $userId, array $kunci, string $aksi): void
    {
        $sekarang = now();
        $kolom = $aksi === 'hapus' ? 'dihapus_at' : 'dibaca_at';

        foreach (array_unique($kunci) as $k) {
            DB::table(self::TABEL)->updateOrInsert(
                ['user_id' => $userId, 'kunci' => $k],
                [$kolom => $sekarang, 'dibaca_at' => $sekarang, 'updated_at' => $sekarang, 'created_at' => $sekarang],
            );
        }

        // Bersih-bersih: catatan lama (> 120 hari) sudah tidak diperlukan
        DB::table(self::TABEL)->where('user_id', $userId)->where('updated_at', '<', $sekarang->copy()->subDays(120))->delete();
    }
}
