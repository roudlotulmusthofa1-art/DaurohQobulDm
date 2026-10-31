<?php

namespace App\Models;

use App\Models\Concerns\IsMasterData;
use Illuminate\Database\Eloquent\Model;

class Kamar extends Model
{
    use IsMasterData;

    protected $table = 'kamar';

    protected $fillable = ['nama', 'kode', 'gedung', 'jenis', 'kapasitas', 'deskripsi', 'urutan', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'urutan' => 'integer',
        'kapasitas' => 'integer',
    ];
}
