<?php

namespace App\Models;

use App\Models\Concerns\IsMasterData;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use IsMasterData;

    protected $table = 'kelas';

    protected $fillable = ['nama', 'kode', 'tingkat', 'deskripsi', 'urutan', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'urutan' => 'integer',
        'tingkat' => 'integer',
    ];
}
