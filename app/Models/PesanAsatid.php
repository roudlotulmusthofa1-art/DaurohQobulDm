<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pesan admin -> asatid. Disimpan di database utama (koneksi "pusat"), berlaku untuk semua sesi.
 * Lihat migrasi create_pesan_asatid_table.
 */
class PesanAsatid extends Model
{
    protected $connection = 'pusat';

    protected $table = 'pesan_asatid';

    protected $fillable = ['user_id', 'pengirim_id', 'judul', 'isi', 'dibaca_at'];

    protected $casts = ['dibaca_at' => 'datetime'];

    /** Akun asatid penerima */
    public function penerima(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Admin pengirim */
    public function pengirim(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengirim_id');
    }

    public function scopeBelumDibaca($q)
    {
        return $q->whereNull('dibaca_at');
    }

    /** Jumlah pesan belum dibaca milik satu akun asatid */
    public static function jumlahBaru(?int $userId): int
    {
        return $userId ? static::where('user_id', $userId)->belumDibaca()->count() : 0;
    }
}
