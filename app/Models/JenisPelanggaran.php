<?php

namespace App\Models;

use App\Models\Concerns\IsMasterData;
use Illuminate\Database\Eloquent\Model;

/** Master Data: jenis pelanggaran santri. */
class JenisPelanggaran extends Model
{
    use IsMasterData;

    protected $table = 'jenis_pelanggaran';

    protected $fillable = ['nama', 'warna', 'deskripsi', 'urutan', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'urutan' => 'integer',
    ];
}
