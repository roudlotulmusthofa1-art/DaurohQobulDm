<?php

namespace App\Support;

use App\Models\Berita;
use App\Models\BeritaFoto;
use App\Models\GaleriKiriman;
use Illuminate\Support\Collection;

/**
 * Isi Galeri Foto (/galeri & landing page):
 * - album yang sudah ditampilkan admin (unggahan admin + kiriman asatid & siswa), satu kotak per album (foto sampul)
 * - foto berita yang sudah ditampilkan admin (tidak disembunyikan dari galeri). Foto berita tidak disalin.
 * Urutan: tanggal terbaru dulu.
 */
class Galeri
{
    /** Saringan halaman /galeri (?asal=) */
    public const ASAL = ['admin', 'kiriman', 'berita'];

    /**
     * @param  string|null  $asal  'admin' | 'kiriman' (asatid & siswa) | 'berita' | null (semua)
     * @return Collection<int, array>
     */
    public static function semua(?string $asal = null, ?int $batas = null): Collection
    {
        $hasil = collect();

        if ($asal !== 'berita') {
            $hasil = $hasil->concat(GaleriKiriman::tampil()->with('foto')->withCount('komentar')
                ->when($asal === 'admin', fn ($q) => $q->where('penulis_peran', 'admin'))
                ->when($asal === 'kiriman', fn ($q) => $q->where('penulis_peran', '!=', 'admin'))
                ->latest('id')->when($batas, fn ($q) => $q->take($batas))->get()
                ->filter(fn ($a) => $a->foto->isNotEmpty())
                ->map(fn (GaleriKiriman $a) => self::barisAlbum($a)));
        }

        if (! in_array($asal, ['admin', 'kiriman'], true)) {
            $hasil = $hasil->concat(self::fotoBerita(false)->when($batas, fn ($q) => $q->take($batas))->get()
                ->map(fn (BeritaFoto $f) => self::barisBerita($f)));
        }

        $hasil = $hasil->sortByDesc(fn ($f) => $f['urut'])->values();

        return $batas ? $hasil->take($batas)->values() : $hasil;
    }

    /** Foto berita yang sudah ditampilkan admin. $termasukTersembunyi = true untuk halaman admin. */
    public static function fotoBerita(bool $termasukTersembunyi = true)
    {
        return BeritaFoto::query()
            ->whereHas('berita', fn ($q) => $q->where('status', Berita::TAMPIL))
            ->when(! $termasukTersembunyi, fn ($q) => $q->where('sembunyi_galeri', false))
            ->with('berita:id,slug,judul,tampil_mulai,created_at')
            ->orderByDesc(
                Berita::select('tampil_mulai')->whereColumn('berita.id', 'berita_foto.berita_id')->limit(1)
            )
            ->orderBy('urutan')->orderBy('id');
    }

    /** Satu album = satu kotak (foto sampul + jumlah foto) */
    public static function barisAlbum(GaleriKiriman $a): array
    {
        $tanggal = $a->tanggal_tampil;

        return [
            'kunci' => 'k' . $a->id,
            'id' => $a->id,
            'asal' => $a->penulis_peran === 'admin' ? 'admin' : 'kiriman',
            'url' => $a->foto->first()?->url,
            'keterangan' => $a->keterangan,
            'tanggal_teks' => $tanggal->locale('id')->translatedFormat('d M Y'),
            'urut' => $tanggal->format('Y-m-d') . ' ' . ($a->tampil_mulai ?? $a->created_at)?->format('H:i:s') . sprintf('%010d', $a->id),
            'album_url' => $a->url,
            'jumlah_foto' => $a->foto->count(),
            'jumlah_suka' => $a->jumlah_suka,
            'jumlah_komentar' => (int) ($a->komentar_count ?? 0),
            'oleh' => $a->penulis_nama,
            'peran' => Berita::labelPeran($a->penulis_peran),
            'berita_url' => null,
            'berita_judul' => null,
        ];
    }

    public static function barisBerita(BeritaFoto $f): array
    {
        $b = $f->berita;
        $tanggal = ($b?->tampil_mulai ?? $b?->created_at)?->copy()->timezone(Berita::ZONA);

        return [
            'kunci' => 'b' . $f->id,
            'id' => $f->id,
            'asal' => 'berita',
            'url' => $f->url,
            'keterangan' => $b?->judul,
            'tanggal' => null,
            'tanggal_teks' => $tanggal?->locale('id')->translatedFormat('d M Y'),
            'urut' => ($tanggal?->format('Y-m-d H:i:s') ?? '') . sprintf('%010d', 9999999999 - (int) $f->urutan),
            'album_url' => null,
            'berita_url' => $b ? route('berita.show', $b->slug) : null,
            'berita_judul' => $b?->judul,
            'sembunyi' => (bool) $f->sembunyi_galeri,
        ];
    }
}
