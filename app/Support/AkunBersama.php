<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Akun admin & asatid dipakai BERSAMA oleh semua sesi.
 * - Nomor id selalu diambil dari database utama (pusat), lalu disalin ke database tiap sesi dengan id yang sama,
 *   sehingga login tetap berlaku walau sesi aktif berganti.
 * - Akun peserta (role siswa) TIDAK disalin: milik sesinya masing-masing.
 * Dipanggil dari event model User (lihat User::booted).
 */
class AkunBersama
{
    public const PERAN = ['admin', 'asatid'];

    /** Id akun peserta di sesi ke-n dimulai dari n × 1.000.000 agar tidak pernah sama dengan akun bersama */
    public const RENTANG_ID = 1_000_000;

    public static function bersama(?string $role): bool
    {
        return in_array($role, self::PERAN, true);
    }

    /** Sebelum akun bersama dibuat di database sesi (bukan utama): pesan id-nya dulu di database utama. */
    public static function pesanId(User $user): void
    {
        if (! self::bersama($user->role) || $user->id || SesiAplikasi::sama($user->getConnectionName() ?? config('database.default'), 'pusat')) {
            return;
        }

        $pusat = DB::connection('pusat')->table('users');
        $data = collect($user->getAttributes())->only(['name', 'email', 'password', 'role', 'role_id'])->all()
            + ['created_at' => now(), 'updated_at' => now()];

        $user->id = $pusat->where('email', $user->email)->value('id')
            ?? DB::connection('pusat')->table('users')->insertGetId($data);
    }

    /** Akun biasa tidak boleh diubah menjadi admin/asatid di database sesi lain (id-nya bukan dari pusat). */
    public static function cegahUbahPeran(User $user): void
    {
        if ($user->isDirty('role') && self::bersama($user->role) && ! self::bersama($user->getOriginal('role'))
            && ! SesiAplikasi::sama($user->getConnectionName() ?? config('database.default'), 'pusat')
            && ! DB::connection('pusat')->table('users')->where('id', $user->id)->exists()) {
            throw new RuntimeException('Akun admin/asatid harus dibuat lewat menu Data Admin atau Data Asatid.');
        }
    }

    /** Setelah akun bersama disimpan: salin ke database utama & semua sesi lain. */
    public static function sebarkan(User $user): void
    {
        if (! self::bersama($user->role)) {
            return;
        }

        $asal = $user->getConnectionName() ?? config('database.default');
        $baris = (array) DB::connection($asal)->table('users')->where('id', $user->id)->first();
        if (! $baris) {
            return;
        }

        foreach (array_keys(SesiAplikasi::semuaKoneksi()) as $koneksi) {
            if (! SesiAplikasi::sama($koneksi, $asal)) {
                self::tulis($koneksi, $baris);
            }
        }
    }

    /** Setelah akun bersama dihapus: hapus juga di database lain. */
    public static function hapus(User $user): void
    {
        if (! self::bersama($user->role)) {
            return;
        }

        $asal = $user->getConnectionName() ?? config('database.default');
        foreach (array_keys(SesiAplikasi::semuaKoneksi()) as $koneksi) {
            if (! SesiAplikasi::sama($koneksi, $asal)) {
                try {
                    DB::connection($koneksi)->table('users')->where('id', $user->id)->delete();
                } catch (Throwable $e) {
                    Log::warning("Hapus akun bersama #{$user->id} di {$koneksi} gagal: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Samakan akun bersama di satu database dengan database utama:
     * salin semua akun admin/asatid dari pusat, hapus akun admin/asatid yang tidak ada di pusat.
     */
    public static function samakan(string $koneksi): int
    {
        if (SesiAplikasi::sama($koneksi, 'pusat')) {
            return 0;
        }

        $akun = DB::connection('pusat')->table('users')->whereIn('role', self::PERAN)->get();
        foreach ($akun as $baris) {
            self::tulis($koneksi, (array) $baris);
        }
        DB::connection($koneksi)->table('users')->whereIn('role', self::PERAN)->whereNotIn('id', $akun->pluck('id'))->delete();

        return $akun->count();
    }

    /** Tulis satu baris akun ke database tujuan (buat baru / perbarui, berdasarkan id) */
    private static function tulis(string $koneksi, array $baris): void
    {
        try {
            $tabel = DB::connection($koneksi)->table('users');
            // Email yang sama sudah dipakai akun lain di sesi itu -> lewati agar data peserta tidak rusak
            if ((clone $tabel)->where('email', $baris['email'])->where('id', '!=', $baris['id'])->exists()) {
                Log::warning("Akun bersama {$baris['email']} tidak disalin ke {$koneksi}: email sudah dipakai akun lain.");

                return;
            }
            $kolom = array_keys($baris);
            DB::connection($koneksi)->table('users')->upsert([$baris], ['id'], array_values(array_diff($kolom, ['id'])));
        } catch (Throwable $e) {
            Log::warning("Salin akun bersama #{$baris['id']} ke {$koneksi} gagal: " . $e->getMessage());
        }
    }
}
