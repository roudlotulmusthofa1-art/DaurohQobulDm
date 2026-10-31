<?php

namespace App\Models;

use App\Models\Concerns\IsMasterData;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class WaktuPelajaran extends Model
{
    use IsMasterData;

    protected $table = 'waktu_pelajaran';

    protected $fillable = ['nama', 'jam_mulai', 'jam_selesai', 'keterangan', 'urutan', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'urutan' => 'integer',
    ];

    /** Jam dikirim sebagai HH:MM agar cocok dengan <input type="time">. */
    protected function jamMulai(): Attribute
    {
        return Attribute::get(fn ($value) => $value ? substr($value, 0, 5) : null);
    }

    protected function jamSelesai(): Attribute
    {
        return Attribute::get(fn ($value) => $value ? substr($value, 0, 5) : null);
    }
}
