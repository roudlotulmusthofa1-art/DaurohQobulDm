<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Komentar pengunjung di halaman detail berita.
 * induk_id terisi = balasan (hanya admin & penulis berita yang boleh membalas, satu tingkat).
 */
class BeritaKomentar extends Model
{
    protected $connection = 'pusat';

    protected $table = 'berita_komentar';

    protected $fillable = ['berita_id', 'induk_id', 'nama', 'isi', 'user_id', 'peran', 'kunci', 'ip'];

    public function berita()
    {
        return $this->belongsTo(Berita::class);
    }

    /** Balasan untuk komentar ini (lama -> baru) */
    public function balasan()
    {
        return $this->hasMany(self::class, 'induk_id')->oldest('id');
    }

    /** Data untuk Vue. $kunci = kode browser yang sedang membuka (untuk tombol hapus milik sendiri) */
    public function baris(?string $kunci = null): array
    {
        $data = [
            'id' => $this->id,
            'nama' => $this->nama,
            'isi' => $this->isi,
            'peran' => Berita::labelPeran($this->peran),
            'waktu' => $this->created_at?->copy()->timezone(Berita::ZONA)->locale('id')->diffForHumans(),
            'milik_saya' => $kunci && $this->kunci === $kunci,
        ];

        if ($this->relationLoaded('balasan')) {
            $data['balasan'] = $this->balasan->map(fn ($b) => $b->baris($kunci))->values()->all();
        }

        return $data;
    }
}
