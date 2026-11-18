<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/** Foto galeri unggahan admin (database utama; file di disk "bersama" folder galeri/). */
class GaleriFoto extends Model
{
    protected $connection = 'pusat';

    protected $table = 'galeri_foto';

    protected $fillable = ['path', 'keterangan', 'tanggal', 'diunggah_oleh'];

    protected $casts = ['tanggal' => 'date'];

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
