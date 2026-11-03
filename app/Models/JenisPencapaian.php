<?php

namespace App\Models;

use App\Models\Concerns\IsMasterData;
use Illuminate\Database\Eloquent\Model;

/** Master Data: jenis pencapaian (prestasi) santri. */
class JenisPencapaian extends Model
{
    use IsMasterData;

    protected $table = 'jenis_pencapaian';

    protected $fillable = ['nama', 'warna', 'deskripsi', 'urutan', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'urutan' => 'integer',
    ];
}
