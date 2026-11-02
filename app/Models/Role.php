<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Role & Permission (Sistem > Role & Permission). Disimpan di database utama (koneksi "pusat").
 * - Role bawaan: admin (Super Admin), asatid, siswa. Tidak bisa dihapus.
 * - Role tambahan = role pengurus admin (mis. Bendahara). Kolom "izin" berisi kunci menu admin
 *   yang boleh dibuka (lihat App\Support\MenuAdmin). Akun admin memakainya lewat kolom users.role_id.
 */
class Role extends Model
{
    protected $connection = 'pusat';

    protected $fillable = ['kunci', 'nama', 'keterangan', 'warna', 'bawaan', 'izin'];

    protected $casts = [
        'bawaan' => 'boolean',
        'izin'   => 'array',
    ];

    /** Pilihan warna lencana role */
    public const WARNA = ['emerald', 'sky', 'amber', 'violet', 'rose', 'indigo', 'teal', 'slate'];

    /** Role yang dipakai untuk akun admin tertentu (null = Super Admin) */
    public static function untukAkun(User $user): ?self
    {
        static $simpan = [];

        if ($user->role !== 'admin' || ! $user->role_id) {
            return null;
        }

        return $simpan[$user->role_id] ??= self::find($user->role_id);
    }

    /**
     * Jumlah akun yang memakai role ini.
     * Admin & asatid dihitung dari database utama (dipakai bersama), peserta dari sesi yang sedang aktif.
     */
    public function jumlahAkun(): int
    {
        if ($this->kunci === 'siswa') {
            return User::where('role', 'siswa')->count();
        }

        $users = \Illuminate\Support\Facades\DB::connection('pusat')->table('users');

        return $this->bawaan
            ? ($this->kunci === 'admin'
                ? $users->where('role', 'admin')->whereNull('role_id')->count()
                : $users->where('role', $this->kunci)->count())
            : $users->where('role', 'admin')->where('role_id', $this->id)->count();
    }
}
