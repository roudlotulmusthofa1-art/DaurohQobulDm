<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Berita untuk landing page (database utama, koneksi "pusat").
 *
 * Alur:
 * - asatid/siswa menulis -> status "menunggu" (Kotak Masuk admin)
 * - admin menekan Tampilkan + batas waktu -> status "tampil" sampai 'tampil_sampai' (kosong = terus)
 * - lewat batas -> otomatis "selesai" (dihitung, tidak disimpan); admin bisa Tampilkan lagi
 * - admin menolak -> "ditolak" (+ catatan untuk penulis)
 * - admin menulis -> langsung "tampil"
 * Waktu disimpan UTC, ditampilkan WIB.
 */
class Berita extends Model
{
    protected $connection = 'pusat';

    protected $table = 'berita';

    public const ZONA = 'Asia/Jakarta';

    public const MENUNGGU = 'menunggu';
    public const TAMPIL = 'tampil';
    public const DITOLAK = 'ditolak';
    /** Status hitungan: tampil tapi batas waktunya sudah lewat */
    public const SELESAI = 'selesai';

    /**
     * Warna label kategori. Master Data menyimpan warna seperti "bg-green-100";
     * yang dipakai hanya nama warnanya ("green"). Kelas Tailwind ditulis lengkap di sini
     * (file ini dibaca Tailwind lewat @source di resources/css/app.css).
     */
    public const KELAS_WARNA = [
        'green' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'emerald' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'teal' => 'bg-teal-50 text-teal-700 ring-teal-200',
        'amber' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'yellow' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'orange' => 'bg-orange-50 text-orange-700 ring-orange-200',
        'blue' => 'bg-sky-50 text-sky-700 ring-sky-200',
        'sky' => 'bg-sky-50 text-sky-700 ring-sky-200',
        'violet' => 'bg-violet-50 text-violet-700 ring-violet-200',
        'purple' => 'bg-violet-50 text-violet-700 ring-violet-200',
        'pink' => 'bg-pink-50 text-pink-700 ring-pink-200',
        'red' => 'bg-rose-50 text-rose-700 ring-rose-200',
        'rose' => 'bg-rose-50 text-rose-700 ring-rose-200',
    ];

    /** "bg-green-100" / "green" -> "green" */
    public static function namaWarna(?string $warna): ?string
    {
        if (! $warna) {
            return null;
        }
        foreach (array_keys(self::KELAS_WARNA) as $nama) {
            if (preg_match('/(^|-)' . $nama . '(-|$)/', $warna)) {
                return $nama;
            }
        }

        return null;
    }

    public function getKelasKategoriAttribute(): string
    {
        return self::KELAS_WARNA[$this->kategori_warna] ?? 'bg-teal-50 text-teal-700 ring-teal-200';
    }

    /** Disk & folder foto (dipakai bersama semua sesi) */
    public const DISK = 'bersama';

    protected $fillable = [
        'slug', 'judul', 'ringkasan', 'isi', 'kategori', 'kategori_warna', 'lokasi', 'lat', 'lng',
        'penulis_user_id', 'penulis_peran', 'penulis_nama', 'penulis_foto', 'sesi_id',
        'status', 'tampil_mulai', 'tampil_sampai', 'catatan_admin', 'diperiksa_oleh', 'diperiksa_at',
        'jumlah_dilihat', 'jumlah_suka',
    ];

    protected $casts = [
        'tampil_mulai' => 'datetime',
        'tampil_sampai' => 'datetime',
        'diperiksa_at' => 'datetime',
        'jumlah_dilihat' => 'integer',
        'jumlah_suka' => 'integer',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function foto(): HasMany
    {
        return $this->hasMany(BeritaFoto::class)->orderBy('urutan')->orderBy('id');
    }

    public function komentar(): HasMany
    {
        return $this->hasMany(BeritaKomentar::class)->latest('id');
    }

    /** Berita yang sedang tampil di landing page */
    public function scopeSedangTampil(Builder $q): Builder
    {
        return $q->where('status', self::TAMPIL)
            ->where(fn ($w) => $w->whereNull('tampil_sampai')->orWhere('tampil_sampai', '>', now()));
    }

    /** menunggu | tampil | selesai | ditolak */
    public function getStatusEfektifAttribute(): string
    {
        if ($this->status === self::TAMPIL && $this->tampil_sampai && $this->tampil_sampai->lte(now())) {
            return self::SELESAI;
        }

        return $this->status;
    }

    public function getSedangTampilAttribute(): bool
    {
        return $this->status_efektif === self::TAMPIL;
    }

    /** Tanggal yang ditampilkan di kartu: sejak tampil (atau saat ditulis) */
    public function getTanggalAttribute(): Carbon
    {
        return ($this->tampil_mulai ?? $this->created_at ?? now())->copy()->timezone(self::ZONA);
    }

    public function getFotoUtamaUrlAttribute(): ?string
    {
        $f = $this->relationLoaded('foto') ? $this->foto->first() : $this->foto()->first();

        return $f?->url;
    }

    /** Ringkasan untuk kartu: ringkasan penulis, atau awal isi berita */
    public function getCuplikanAttribute(): string
    {
        return $this->ringkasan ?: Str::limit(trim(preg_replace('/\s+/', ' ', $this->isi)), 160);
    }

    /** Isi berita: dipisah per paragraf (baris kosong / baris baru), sudah aman ditampilkan */
    public function getParagrafAttribute(): array
    {
        return collect(preg_split("/\r\n|\n|\r/", (string) $this->isi))
            ->map(fn ($p) => trim($p))->filter()->values()->all();
    }

    /** Ada titik peta? */
    public function getPunyaPetaAttribute(): bool
    {
        return $this->lat !== null && $this->lng !== null;
    }

    public function getUrlMapsAttribute(): ?string
    {
        return $this->punya_peta ? 'https://www.google.com/maps?q=' . (float) $this->lat . ',' . (float) $this->lng : null;
    }

    /** Peta kecil OpenStreetMap (iframe) dengan penanda di titik lokasi */
    public function getUrlPetaAttribute(): ?string
    {
        if (! $this->punya_peta) {
            return null;
        }
        $lat = (float) $this->lat;
        $lng = (float) $this->lng;
        $d = 0.01;

        return 'https://www.openstreetmap.org/export/embed.html?bbox=' . ($lng - $d) . '%2C' . ($lat - $d) . '%2C' . ($lng + $d) . '%2C' . ($lat + $d)
            . '&layer=mapnik&marker=' . $lat . '%2C' . $lng;
    }

    public function getUrlAttribute(): string
    {
        return route('berita.show', $this->slug);
    }

    public static function labelPeran(?string $peran): string
    {
        return match ($peran) {
            'admin' => __('Admin'),
            'asatid' => __('Asatid'),
            'siswa' => __('Siswa'),
            default => '',
        };
    }

    /**
     * Jumlah berita milik satu penulis (kartu Tulis Berita di beranda asatid/siswa).
     * Akun siswa milik sesinya masing-masing, jadi dicocokkan juga dengan sesi aktif.
     *
     * @return array{tampil: int, menunggu: int}
     */
    public static function jumlahMilik(string $peran, int $userId): array
    {
        return rescue(function () use ($peran, $userId) {
            $q = static::where('penulis_peran', $peran)->where('penulis_user_id', $userId)
                ->when($peran === 'siswa', fn ($w) => $w->where('sesi_id', \App\Support\SesiAplikasi::aktif()?->id));

            return [
                'tampil' => (clone $q)->sedangTampil()->count(),
                'menunggu' => (clone $q)->where('status', self::MENUNGGU)->count(),
            ];
        }, ['tampil' => 0, 'menunggu' => 0], false);
    }

    /** Alamat unik dari judul: judul-berita, judul-berita-2, ... */
    public static function buatSlug(string $judul, ?int $kecuali = null): string
    {
        $dasar = Str::limit(Str::slug($judul), 180, '') ?: 'berita';
        $slug = $dasar;
        $i = 2;
        while (static::where('slug', $slug)->when($kecuali, fn ($q) => $q->where('id', '!=', $kecuali))->exists()) {
            $slug = $dasar . '-' . $i++;
        }

        return $slug;
    }

    /** Hapus berita beserta file fotonya */
    public function hapusSemua(): void
    {
        foreach ($this->foto as $f) {
            Storage::disk(self::DISK)->delete($f->path);
        }
        $this->delete();
    }

    /** Data untuk daftar Vue (admin & Berita Saya) */
    public function baris(): array
    {
        $fmt = fn ($t) => $t?->copy()->timezone(self::ZONA)->locale('id')->translatedFormat('d M Y, H:i');

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'url' => $this->url,
            'judul' => $this->judul,
            'ringkasan' => $this->ringkasan,
            'isi' => $this->isi,
            'kategori' => $this->kategori,
            'kategori_warna' => $this->kategori_warna,
            'kelas_kategori' => $this->kelas_kategori,
            'lokasi' => $this->lokasi,
            'lat' => $this->lat !== null ? (float) $this->lat : null,
            'lng' => $this->lng !== null ? (float) $this->lng : null,
            'penulis_nama' => $this->penulis_nama,
            'penulis_peran' => $this->penulis_peran,
            'penulis_label' => self::labelPeran($this->penulis_peran),
            'penulis_foto' => $this->penulis_foto,
            'status' => $this->status_efektif,
            'tampil_mulai' => $fmt($this->tampil_mulai),
            'tampil_sampai' => $fmt($this->tampil_sampai),
            'tampil_sampai_tanggal' => $this->tampil_sampai?->copy()->timezone(self::ZONA)->format('Y-m-d'),
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
