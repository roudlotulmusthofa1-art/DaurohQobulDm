<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Pesan admin <-> peserta. Lihat migrasi create_pesan_peserta_table. */
class PesanPeserta extends Model
{
    protected $table = 'pesan_peserta';

    public const DARI_ADMIN = 'admin';

    public const DARI_PESERTA = 'peserta';

    protected $fillable = ['pendaftaran_siswa_id', 'dari', 'user_id', 'balasan_untuk_id', 'judul', 'isi', 'dibaca_at'];

    protected $casts = ['dibaca_at' => 'datetime'];

    public function peserta(): BelongsTo
    {
        return $this->belongsTo(PendaftaranSiswa::class, 'pendaftaran_siswa_id');
    }

    public function pengirim(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
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

    public function scopeDariPeserta($q)
    {
        return $q->where('dari', self::DARI_PESERTA);
    }

    public function scopeBelumDibaca($q)
    {
        return $q->whereNull('dibaca_at');
    }
}
