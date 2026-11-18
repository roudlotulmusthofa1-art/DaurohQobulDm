<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/** Foto berita (disk "bersama", folder berita/{id_berita}/). */
class BeritaFoto extends Model
{
    protected $connection = 'pusat';

    protected $table = 'berita_foto';

    protected $fillable = ['berita_id', 'path', 'urutan', 'sembunyi_galeri'];

    /** sembunyi_galeri: disembunyikan admin dari Galeri Foto (beritanya tidak berubah) */
    protected $casts = ['sembunyi_galeri' => 'boolean'];

    public function berita()
    {
        return $this->belongsTo(Berita::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk(Berita::DISK)->url($this->path);
    }
}
