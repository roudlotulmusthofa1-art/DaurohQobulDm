<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Komentar pengunjung di halaman album galeri (/galeri/{id}).
 * induk_id terisi = balasan (hanya admin & pengirim album, satu tingkat).
 */
class GaleriKomentar extends Model
{
    protected $connection = 'pusat';

    protected $table = 'galeri_komentar';

    protected $fillable = ['kiriman_id', 'induk_id', 'nama', 'isi', 'user_id', 'peran', 'kunci', 'ip'];

    public function kiriman()
    {
        return $this->belongsTo(GaleriKiriman::class, 'kiriman_id');
    }

    public function balasan()
    {
        return $this->hasMany(self::class, 'induk_id')->oldest('id');
    }

    /** Data untuk Vue. $kunci = kode browser; $admin = admin boleh menghapus semua komentar */
    public function baris(?string $kunci = null, bool $admin = false): array
    {
        $data = [
            'id' => $this->id,
            'nama' => $this->nama,
            'isi' => $this->isi,
            'peran' => Berita::labelPeran($this->peran),
            'waktu' => $this->created_at?->copy()->timezone(Berita::ZONA)->locale('id')->diffForHumans(),
            'boleh_hapus' => $admin || ($kunci && $this->kunci === $kunci),
        ];

        if ($this->relationLoaded('balasan')) {
            $data['balasan'] = $this->balasan->map(fn ($b) => $b->baris($kunci, $admin))->values()->all();
        }

        return $data;
    }
}
