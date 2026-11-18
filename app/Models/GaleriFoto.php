<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Foto galeri (database utama; file di disk "bersama" folder galeri/).
 * Setiap foto milik satu album (GaleriKiriman). Kolom keterangan & tanggal di sini sisa versi lama;
 * yang dipakai sekarang keterangan & tanggal album.
 */
class GaleriFoto extends Model
{
    protected $connection = 'pusat';

    protected $table = 'galeri_foto';

    protected $fillable = ['kiriman_id', 'path', 'urutan', 'keterangan', 'tanggal', 'diunggah_oleh'];

    protected $casts = ['tanggal' => 'date'];

    public function kiriman()
    {
        return $this->belongsTo(GaleriKiriman::class, 'kiriman_id');
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk(Berita::DISK)->url($this->path);
    }

    public function hapusSemua(): void
    {
        Storage::disk(Berita::DISK)->delete($this->path);
        $this->delete();
    }
}
