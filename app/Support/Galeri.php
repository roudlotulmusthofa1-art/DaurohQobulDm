<?php

namespace App\Support;

use App\Models\Berita;
use App\Models\BeritaFoto;
use App\Models\GaleriFoto;
use Illuminate\Support\Collection;

/**
 * Isi Galeri Foto = foto unggahan admin (galeri_foto) + foto berita yang sudah ditampilkan admin
 * (sedang tampil maupun selesai tayang, tidak disembunyikan dari galeri). Foto berita tidak disalin.
 * Urutan: tanggal terbaru dulu.
 */
class Galeri
{
    /**
     * @param  string|null  $asal  'admin' | 'berita' | null (semua)
     * @return Collection<int, array>
     */
    public static function semua(?string $asal = null, ?int $batas = null): Collection
    {
        $hasil = collect();

        if ($asal !== 'berita') {
            $hasil = $hasil->concat(GaleriFoto::latest('id')->when($batas, fn ($q) => $q->take($batas))->get()
                ->map(fn (GaleriFoto $f) => self::barisAdmin($f)));
        }

        if ($asal !== 'admin') {
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

    public static function barisAdmin(GaleriFoto $f): array
    {
        $tanggal = $f->tanggal ?? $f->created_at?->copy()->timezone(Berita::ZONA);

        return [
            'kunci' => 'a' . $f->id,
            'id' => $f->id,
            'asal' => 'admin',
            'url' => $f->url,
            'keterangan' => $f->keterangan,
            'tanggal' => $f->tanggal?->format('Y-m-d'),
            'tanggal_teks' => $tanggal?->locale('id')->translatedFormat('d M Y'),
            'urut' => ($tanggal?->format('Y-m-d') ?? '') . ' ' . $f->created_at?->format('H:i:s') . sprintf('%010d', $f->id),
            'berita_url' => null,
            'berita_judul' => null,
            'oleh' => $f->diunggah_oleh,
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
            'berita_url' => $b ? route('berita.show', $b->slug) : null,
            'berita_judul' => $b?->judul,
            'sembunyi' => (bool) $f->sembunyi_galeri,
        ];
    }
}
