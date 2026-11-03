<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Pita Pengumuman di paling atas landing page (Admin > Landing Page > Pita Pengumuman).
 * Tampil otomatis sesuai jadwal: sejak 'tampil_sejak' (atau 'mulai_at') sampai 'selesai_at'.
 * Waktu disimpan UTC, ditampilkan dalam WIB.
 */
class PitaPengumuman extends Model
{
    protected $connection = 'pusat';

    protected $table = 'pita_pengumuman';

    public const ZONA = 'Asia/Jakarta';

    public const JENIS = [
        'pendaftaran' => 'Pendaftaran',
        'dauroh' => 'Masa Dauroh',
        'info' => 'Info',
    ];

    /** Jenis tambahan buatan admin (disimpan di Pengaturan database utama, kunci 'pita_jenis_tambahan', JSON daftar nama) */
    public static function jenisTambahan(): array
    {
        $isi = rescue(fn () => Pengaturan::ambilPusat('pita_jenis_tambahan'), null, false);
        $daftar = json_decode((string) $isi, true);

        return is_array($daftar) ? array_values(array_filter($daftar, 'is_string')) : [];
    }

    public static function simpanJenisTambahan(array $daftar): void
    {
        Pengaturan::simpanPusat('pita_jenis_tambahan', json_encode(array_values(array_unique($daftar)), JSON_UNESCAPED_UNICODE));
    }

    /** Semua jenis: bawaan + tambahan. kunci => nama (jenis tambahan: kunci = namanya) */
    public static function semuaJenis(): array
    {
        $tambahan = self::jenisTambahan();

        return self::JENIS + array_combine($tambahan, $tambahan);
    }

    /** Tempat pita tampil */
    public const TUJUAN = [
        'landing' => 'Landing Page',
        'asatid' => 'Dasbor Asatid',
        'siswa' => 'Dasbor Peserta',
    ];

    /** Kunci warna (kelas Tailwind-nya ada di partials/pita-pengumuman-gaya.blade.php); 'sendiri' = pakai warna_hex */
    public const WARNA = ['hijau', 'emas', 'biru', 'merah', 'ungu', 'gelap', 'sendiri'];

    /** Tampilan pita untuk semua pengumuman (disimpan di Pengaturan database utama) */
    public const POSISI = ['atas' => 'Di atas top bar', 'bawah' => 'Di bawah top bar'];

    public const UKURAN = ['penuh' => 'Penuh (selebar layar)', 'ringkas' => 'Ringkas (kecil di tengah)'];

    /** @return array{posisi: string, ukuran: string} */
    public static function tampilan(): array
    {
        $posisi = rescue(fn () => Pengaturan::ambilPusat('pita_posisi'), null, false);
        $ukuran = rescue(fn () => Pengaturan::ambilPusat('pita_ukuran'), null, false);

        return [
            'posisi' => array_key_exists((string) $posisi, self::POSISI) ? $posisi : 'bawah',
            'ukuran' => array_key_exists((string) $ukuran, self::UKURAN) ? $ukuran : 'ringkas',
        ];
    }

    protected $fillable = [
        'jenis', 'tujuan', 'judul', 'keterangan', 'mulai_at', 'selesai_at', 'tampil_sejak',
        'hitung_mundur', 'tombol_teks', 'tombol_url', 'warna', 'warna_hex', 'urutan', 'is_active',
    ];

    protected $casts = [
        'mulai_at' => 'datetime',
        'selesai_at' => 'datetime',
        'tampil_sejak' => 'datetime',
        'hitung_mundur' => 'boolean',
        'is_active' => 'boolean',
        'urutan' => 'integer',
    ];

    /** Yang sedang boleh tampil sekarang di tempat tertentu (landing / asatid / siswa) */
    public function scopeTampilSekarang(Builder $q, string $tujuan = 'landing'): Builder
    {
        $kini = now();

        return $q->where('tujuan', $tujuan)->where('is_active', true)
            ->where('selesai_at', '>', $kini)
            ->where(fn ($w) => $w
                ->where(fn ($a) => $a->whereNotNull('tampil_sejak')->where('tampil_sejak', '<=', $kini))
                ->orWhere(fn ($b) => $b->whereNull('tampil_sejak')->where('mulai_at', '<=', $kini)))
            ->orderBy('urutan')->orderBy('mulai_at');
    }

    /** Teks rentang waktu dalam WIB, mis. "10 Nov 2026, 08.00 – 20 Nov 2026, 23.59 WIB" */
    public function rentang(): string
    {
        $m = $this->mulai_at->copy()->setTimezone(self::ZONA)->locale('id');
        $s = $this->selesai_at->copy()->setTimezone(self::ZONA)->locale('id');

        if ($m->isSameDay($s)) {
            return $m->translatedFormat('l, j M Y') . ' · ' . $m->format('H.i') . '–' . $s->format('H.i') . ' WIB';
        }

        return $m->translatedFormat('j M Y, H.i') . ' – ' . $s->translatedFormat('j M Y, H.i') . ' WIB';
    }

    /** Status sekarang: nonaktif | menunggu (belum tampil) | akan (tampil, belum mulai) | berlangsung | selesai */
    public function status(): string
    {
        $kini = now();

        return match (true) {
            ! $this->is_active => 'nonaktif',
            $this->selesai_at <= $kini => 'selesai',
            $this->mulai_at <= $kini => 'berlangsung',
            ($this->tampil_sejak ?? $this->mulai_at) <= $kini => 'akan',
            default => 'menunggu',
        };
    }

    /** Data untuk landing page (Vue) */
    public function untukPublik(): array
    {
        return [
            'id' => $this->id,
            'versi' => $this->id . '-' . $this->updated_at?->timestamp, // tombol tutup: muncul lagi bila diubah
            'jenis' => $this->jenis,
            'tujuan' => $this->tujuan ?: 'landing',
            'label' => self::JENIS[$this->jenis] ?? ($this->jenis ?: 'Info'), // jenis tambahan: namanya sendiri
            'judul' => $this->judul,
            'keterangan' => $this->keterangan,
            'warna' => $this->warna,
            'warna_hex' => $this->warna_hex,
            'hitung_mundur' => $this->hitung_mundur,
            'tombol_teks' => $this->tombol_teks,
            'tombol_url' => $this->tombol_url,
            'mulai' => $this->mulai_at->toIso8601String(),
            'selesai' => $this->selesai_at->toIso8601String(),
            'rentang' => $this->rentang(),
        ];
    }

    /** Data untuk halaman admin: + isian form (format datetime-local, WIB) & status */
    public function untukAdmin(): array
    {
        $lokal = fn (?Carbon $t) => $t?->copy()->setTimezone(self::ZONA)->format('Y-m-d\TH:i');

        return $this->untukPublik() + [
            'urutan' => $this->urutan,
            'is_active' => $this->is_active,
            'status' => $this->status(),
            'mulai_lokal' => $lokal($this->mulai_at),
            'selesai_lokal' => $lokal($this->selesai_at),
            'tampil_sejak_lokal' => $lokal($this->tampil_sejak),
        ];
    }
}
