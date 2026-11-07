<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tanda "sudah pergantian pelajaran" dari admin untuk satu jadwal mengajar pada satu tanggal.
 * Dikirim dari dasbor admin (Admin\PergantianPelajaranController), dilihat guru di web asatid.
 */
class TandaPelajaran extends Model
{
    protected $table = 'tanda_pelajaran';

    protected $fillable = ['jadwal_mengajar_id', 'user_id', 'tanggal', 'dikirim_oleh', 'dikirim_at', 'dilihat_at'];

    protected $casts = [
        'tanggal'    => 'date',
        'dikirim_at' => 'datetime',
        'dilihat_at' => 'datetime',
    ];

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(JadwalMengajar::class, 'jadwal_mengajar_id');
    }

    /** Guru yang diberi tanda */
    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Admin yang mengirim */
    public function pengirim(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dikirim_oleh');
    }
}
