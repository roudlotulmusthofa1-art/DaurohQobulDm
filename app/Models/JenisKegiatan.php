<?php

namespace App\Models;

use App\Models\Concerns\IsMasterData;
use Illuminate\Database\Eloquent\Model;

/** Master Data: jenis/nama kegiatan pondok (mis. Muhadharah, Ro'an, Ziarah). */
class JenisKegiatan extends Model
{
    use IsMasterData;

    protected $table = 'jenis_kegiatan';

    protected $fillable = ['nama', 'warna', 'deskripsi', 'urutan', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'urutan' => 'integer',
    ];
}
