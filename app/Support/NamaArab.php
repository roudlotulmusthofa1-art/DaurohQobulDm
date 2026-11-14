<?php

namespace App\Support;

use App\Models\PendaftaranAdmin;
use App\Models\PendaftaranAsatid;
use App\Models\PendaftaranSiswa;
use Throwable;

/**
 * Nama Arab pemilik sebuah akun (users.id) — admin, asatid, atau santri.
 * Tabel users tidak punya nama Arab; nama Arab ada di PendaftaranAdmin / PendaftaranAsatid (database pusat)
 * dan PendaftaranSiswa (database sesi). Urutan: admin, lalu asatid, lalu santri.
 *
 * Dipakai untuk nama "oleh" (penilai, pencatat, pengabsen, pengirim pesan) dan nama admin yang login.
 * - Daftar   : NamaArab::untukUser($baris->pluck('dinilai_oleh')) sekali di awal (1 kueri per tabel),
 *              lalu NamaArab::user($id) di tiap baris (diambil dari simpanan, tanpa kueri lagi).
 * - Satu akun: NamaArab::user($id) atau $user->nama_arab
 * Hasil disimpan selama satu permintaan (request), jadi tidak dicari berulang.
 */
class NamaArab
{
    /** user_id => nama Arab (null = tidak ada) */
    private static array $simpanan = [];

    /** Cari nama Arab beberapa akun sekaligus. Hasil: [user_id => nama Arab] (yang tidak punya tidak ikut). */
    public static function untukUser(iterable $userIds): array
    {
        $ids = collect($userIds)->filter()->map(fn ($id) => (int) $id)->unique();
        $belum = $ids->reject(fn ($id) => array_key_exists($id, self::$simpanan))->values()->all();

        if ($belum) {
            $hasil = [];
            foreach ([PendaftaranAdmin::class, PendaftaranAsatid::class, PendaftaranSiswa::class] as $model) {
                try {
                    // "+=" : yang sudah ditemukan (admin/asatid) tidak ditimpa
                    $hasil += $model::query()->whereIn('user_id', $belum)
                        ->whereNotNull('nama_lengkap_arab')->where('nama_lengkap_arab', '!=', '')
                        ->pluck('nama_lengkap_arab', 'user_id')->all();
                } catch (Throwable) {
                    // tabel belum ada (database sesi baru): lewati
                }
            }
            foreach ($belum as $id) {
                self::$simpanan[$id] = $hasil[$id] ?? null;
            }
        }

        return array_filter(array_intersect_key(self::$simpanan, array_flip($ids->all())));
    }

    /** Nama Arab satu akun (null kalau tidak ada / belum diisi) */
    public static function user($userId): ?string
    {
        if (! $userId) {
            return null;
        }

        return self::untukUser([$userId])[(int) $userId] ?? null;
    }
}
