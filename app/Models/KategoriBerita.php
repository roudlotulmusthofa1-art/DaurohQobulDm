<?php

namespace App\Models;

use App\Models\Concerns\IsMasterData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class KategoriBerita extends Model
{
    use IsMasterData;

    protected $table = 'kategori_berita';

    protected $fillable = ['nama', 'slug', 'warna', 'deskripsi', 'urutan', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'urutan' => 'integer',
    ];

    /** Berita dalam kategori ini (kolom berita.kategori_berita_id). */
    public function berita(): HasMany
    {
        return $this->hasMany(\App\Models\Berita::class, 'kategori_berita_id');
    }

    public static function countRelation(): ?string
    {
        return class_exists(\App\Models\Berita::class)
            && Schema::hasColumn((new \App\Models\Berita)->getTable(), 'kategori_berita_id')
            ? 'berita' : null;
    }
}
