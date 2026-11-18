<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Album Galeri Foto (database utama, koneksi "pusat").
 *
 * Alur (seperti berita):
 * - asatid/siswa mengirim (menu Kirim Foto) -> "menunggu" (Admin > Galeri Foto > Kotak Masuk)
 * - admin menekan Tampilkan -> "tampil" di /galeri (bisa disukai, dikomentari, dibagikan)
 * - admin menolak -> "ditolak" (+ catatan); pengirim bisa memperbaiki lalu mengirim ulang
 * - admin mengunggah -> langsung "tampil"
 */
class GaleriKiriman extends Model
{
    protected $connection = 'pusat';

    protected $table = 'galeri_kiriman';

    public const MENUNGGU = 'menunggu';
    public const TAMPIL = 'tampil';
    public const DITOLAK = 'ditolak';

    /** Batas foto per album */
    public const MAKS_FOTO = 30;

    protected $fillable = [
        'keterangan', 'tanggal',
        'penulis_user_id', 'penulis_peran', 'penulis_nama', 'penulis_foto', 'sesi_id',
        'status', 'tampil_mulai', 'catatan_admin', 'diperiksa_oleh', 'diperiksa_at',
        'jumlah_dilihat', 'jumlah_suka',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'tampil_mulai' => 'datetime',
        'diperiksa_at' => 'datetime',
        'jumlah_dilihat' => 'integer',
        'jumlah_suka' => 'integer',
    ];

    public function foto(): HasMany
    {
        return $this->hasMany(GaleriFoto::class, 'kiriman_id')->orderBy('urutan')->orderBy('id');
    }

    public function komentar(): HasMany
    {
        return $this->hasMany(GaleriKomentar::class, 'kiriman_id')->latest('id');
    }

    public function scopeTampil(Builder $q): Builder
    {
        return $q->where('status', self::TAMPIL);
    }

    /** Tanggal kegiatan, atau tanggal tampil / kirim (WIB) */
    public function getTanggalTampilAttribute(): Carbon
    {
        return $this->tanggal ?? ($this->tampil_mulai ?? $this->created_at ?? now())->copy()->timezone(Berita::ZONA);
    }

    public function getUrlAttribute(): string
    {
        return route('galeri.show', $this->id);
    }

    public function getFotoUtamaUrlAttribute(): ?string
    {
        $f = $this->relationLoaded('foto') ? $this->foto->first() : $this->foto()->first();

        return $f?->url;
    }

    /** Foto pengirim: alamat lama yang tersimpan lengkap disesuaikan dengan alamat yang sedang dibuka */
    public function getPenulisFotoAttribute(?string $nilai): ?string
    {
        return $nilai && str_contains($nilai, '/storage/')
            ? asset('storage/' . Str::after($nilai, '/storage/'))
            : $nilai;
    }

    /** Judul singkat untuk bagikan / notifikasi */
    public function getJudulAttribute(): string
    {
        return $this->keterangan ? Str::limit($this->keterangan, 80) : __('Album foto') . ' ' . $this->tanggal_tampil->locale('id')->translatedFormat('d M Y');
    }

    /**
     * Jumlah album milik satu pengirim (kartu Kirim Foto di beranda asatid/siswa).
     *
     * @return array{tampil: int, menunggu: int}
     */
    public static function jumlahMilik(string $peran, int $userId): array
    {
        return rescue(function () use ($peran, $userId) {
            $q = static::where('penulis_peran', $peran)->where('penulis_user_id', $userId)
                ->when($peran === 'siswa', fn ($w) => $w->where('sesi_id', \App\Support\SesiAplikasi::aktif()?->id));

            return [
                'tampil' => (clone $q)->tampil()->count(),
                'menunggu' => (clone $q)->where('status', self::MENUNGGU)->count(),
            ];
        }, ['tampil' => 0, 'menunggu' => 0], false);
    }

    /** Hapus album beserta file fotonya */
    public function hapusSemua(): void
    {
        foreach ($this->foto as $f) {
            Storage::disk(Berita::DISK)->delete($f->path);
        }
        $this->delete();
    }

    /** Data untuk daftar Vue (admin & Kirim Foto) */
    public function baris(): array
    {
        $fmt = fn ($t) => $t?->copy()->timezone(Berita::ZONA)->locale('id')->translatedFormat('d M Y, H:i');

        return [
            'id' => $this->id,
            'url' => $this->url,
            'keterangan' => $this->keterangan,
            'tanggal' => $this->tanggal?->format('Y-m-d'),
            'tanggal_teks' => $this->tanggal_tampil->locale('id')->translatedFormat('d M Y'),
            'penulis_nama' => $this->penulis_nama,
            'penulis_peran' => $this->penulis_peran,
            'penulis_label' => Berita::labelPeran($this->penulis_peran),
            'penulis_foto' => $this->penulis_foto,
            'status' => $this->status,
            'catatan_admin' => $this->catatan_admin,
            'diperiksa_oleh' => $this->diperiksa_oleh,
            'diperiksa_at' => $fmt($this->diperiksa_at),
            'dibuat' => $fmt($this->created_at),
            'jumlah_dilihat' => $this->jumlah_dilihat,
            'jumlah_suka' => $this->jumlah_suka,
            'jumlah_komentar' => (int) ($this->komentar_count ?? 0),
            'foto' => $this->foto->map(fn ($f) => ['id' => $f->id, 'url' => $f->url])->values()->all(),
        ];
    }
}
