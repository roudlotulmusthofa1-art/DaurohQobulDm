<?php

namespace App\Models;

use App\Models\Concerns\IsMasterData;
use Illuminate\Database\Eloquent\Model;

/**
 * Master Data: semester per tahun ajaran, dengan tanggal Masehi dan Hijriyah.
 * Tanggal Hijriyah diisi otomatis dari tanggal Masehi di form (bisa diubah manual).
 */
class Semester extends Model
{
    use IsMasterData;

    protected $table = 'semester';

    protected $fillable = [
        'nama',
        'tahun_ajaran', 'tanggal_mulai', 'tanggal_selesai',                                  // Masehi
        'tahun_ajaran_hijriyah', 'tanggal_mulai_hijriyah', 'tanggal_selesai_hijriyah',       // Hijriyah
        'keterangan', 'urutan', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'urutan' => 'integer',
        // date:Y-m-d -> dikirim sebagai "2026-07-13", cocok untuk <input type="date">
        'tanggal_mulai' => 'date:Y-m-d',
        'tanggal_selesai' => 'date:Y-m-d',
    ];
}
