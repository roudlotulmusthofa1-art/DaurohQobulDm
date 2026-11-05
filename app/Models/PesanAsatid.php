<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Pesan dua arah admin <-> asatid. Disimpan di database utama (koneksi "pusat"), berlaku untuk semua sesi.
 * - user_id     : akun asatid (pemilik percakapan), baik penerima maupun pengirim
 * - dari        : 'admin' (admin -> asatid) atau 'asatid' (asatid -> admin)
 * - pengirim_id : admin pengirim (hanya untuk dari = 'admin')
 * Lihat migrasi create_pesan_asatid_table & tambah_dari_ke_pesan_asatid.
 */
class PesanAsatid extends Model
{
    protected $connection = 'pusat';

    protected $table = 'pesan_asatid';

    public const DARI_ADMIN = 'admin';

    public const DARI_ASATID = 'asatid';

    protected $fillable = ['user_id', 'pengirim_id', 'dari', 'balasan_untuk_id', 'judul', 'isi', 'dibaca_at'];

    protected $casts = ['dibaca_at' => 'datetime'];

    /** Akun asatid pemilik percakapan */
    public function penerima(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Admin pengirim */
    public function pengirim(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengirim_id');
    }

    public function dibalas(): BelongsTo
    {
        return $this->belongsTo(self::class, 'balasan_untuk_id');
    }

    public function balasan(): HasMany
    {
        return $this->hasMany(self::class, 'balasan_untuk_id')->orderBy('id');
    }

    public function scopeDariAdmin($q)
    {
        return $q->where('dari', self::DARI_ADMIN);
    }

    public function scopeDariAsatid($q)
    {
        return $q->where('dari', self::DARI_ASATID);
    }

    public function scopeBelumDibaca($q)
    {
        return $q->whereNull('dibaca_at');
    }

    /** Jumlah pesan dari admin yang belum dibaca oleh satu akun asatid */
    public static function jumlahBaru(?int $userId): int
    {
        return $userId ? static::where('user_id', $userId)->dariAdmin()->belumDibaca()->count() : 0;
    }
}
