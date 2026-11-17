<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Tanda santri sudah hafal SELURUH satu kitab (tombol paling bawah di halaman Hafalan). */
class HafalanSelesai extends Model
{
    protected $table = 'hafalan_selesai';

    protected $fillable = ['pendaftaran_siswa_id', 'kitab_id', 'ditandai_oleh', 'ditandai_pada'];

    protected $casts = ['ditandai_pada' => 'datetime'];

    public function penanda(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditandai_oleh');
    }
}
