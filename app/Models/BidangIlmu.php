<?php

namespace App\Models;

use App\Models\Concerns\IsMasterData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class BidangIlmu extends Model
{
    use IsMasterData;

    protected $table = 'bidang_ilmu';

    protected $fillable = ['nama', 'slug', 'warna', 'deskripsi', 'urutan', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'urutan' => 'integer',
    ];

    /** Kitab dalam bidang ilmu ini (kolom kitab.bidang_ilmu_id). */
    public function kitab(): HasMany
    {
        return $this->hasMany(\App\Models\Kitab::class, 'bidang_ilmu_id');
    }

    /** Hanya dihitung jika model Kitab & kolomnya sudah ada. */
    public static function countRelation(): ?string
    {
        return class_exists(\App\Models\Kitab::class)
            && Schema::hasColumn((new \App\Models\Kitab)->getTable(), 'bidang_ilmu_id')
            ? 'kitab' : null;
    }
}
