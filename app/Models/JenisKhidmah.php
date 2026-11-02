<?php

namespace App\Models;

use App\Models\Concerns\IsMasterData;
use Illuminate\Database\Eloquent\Model;

/** Master Data: jenis tugas khidmah/pengabdian santri (mis. Kebersihan, Dapur, Keamanan). */
class JenisKhidmah extends Model
{
    use IsMasterData;

    protected $table = 'jenis_khidmah';

    protected $fillable = ['nama', 'warna', 'deskripsi', 'urutan', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'urutan' => 'integer',
    ];
}
