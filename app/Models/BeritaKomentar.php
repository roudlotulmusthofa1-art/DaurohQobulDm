<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Komentar pengunjung di halaman detail berita. */
class BeritaKomentar extends Model
{
    protected $connection = 'pusat';

    protected $table = 'berita_komentar';

    protected $fillable = ['berita_id', 'nama', 'isi', 'user_id', 'peran', 'kunci', 'ip'];

    public function berita()
    {
        return $this->belongsTo(Berita::class);
    }

    /** Data untuk Vue. $kunci = kode browser yang sedang membuka (untuk tombol hapus milik sendiri) */
    public function baris(?string $kunci = null): array
    {
        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'isi' => $this->isi,
            'peran' => Berita::labelPeran($this->peran),
            'waktu' => $this->created_at?->copy()->timezone(Berita::ZONA)->locale('id')->diffForHumans(),
            'milik_saya' => $kunci && $this->kunci === $kunci,
        ];
    }
}
