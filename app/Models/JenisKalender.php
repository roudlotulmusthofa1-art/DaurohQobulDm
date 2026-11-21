<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

/**
 * Jenis kegiatan Kalender Pendidikan, dibuat admin sendiri (tombol "Jenis & Warna"): nama + warna penanda.
 * Kegiatan memakai jenis_id; jenis dihapus = kegiatannya jadi "Tanpa jenis" (abu-abu).
 */
class JenisKalender extends Model
{
    protected $table = 'jenis_kalender';

    protected $fillable = ['nama', 'warna', 'urutan'];

    protected $casts = ['urutan' => 'integer'];

    /** Warna yang bisa dipilih: kunci => label */
    public const WARNA = [
        'emerald' => 'Hijau', 'teal' => 'Hijau toska', 'sky' => 'Biru langit', 'blue' => 'Biru',
        'indigo' => 'Nila', 'violet' => 'Ungu', 'pink' => 'Merah muda', 'rose' => 'Merah',
        'orange' => 'Oranye', 'amber' => 'Kuning', 'lime' => 'Hijau muda', 'slate' => 'Abu-abu',
    ];

    /**
     * Kelas Tailwind per warna (ditulis lengkap supaya terbaca saat build, lewat @source di app.css):
     * pita = latar pita/lencana, titik = bulatan penanda, garis = garis kemajuan.
     */
    public const KELAS_WARNA = [
        'emerald' => ['pita' => 'bg-emerald-100 text-emerald-800 ring-emerald-200', 'titik' => 'bg-emerald-500', 'garis' => 'bg-emerald-500'],
        'teal'    => ['pita' => 'bg-teal-100 text-teal-800 ring-teal-200', 'titik' => 'bg-teal-500', 'garis' => 'bg-teal-500'],
        'sky'     => ['pita' => 'bg-sky-100 text-sky-800 ring-sky-200', 'titik' => 'bg-sky-500', 'garis' => 'bg-sky-500'],
        'blue'    => ['pita' => 'bg-blue-100 text-blue-800 ring-blue-200', 'titik' => 'bg-blue-500', 'garis' => 'bg-blue-500'],
        'indigo'  => ['pita' => 'bg-indigo-100 text-indigo-800 ring-indigo-200', 'titik' => 'bg-indigo-500', 'garis' => 'bg-indigo-500'],
        'violet'  => ['pita' => 'bg-violet-100 text-violet-800 ring-violet-200', 'titik' => 'bg-violet-500', 'garis' => 'bg-violet-500'],
        'pink'    => ['pita' => 'bg-pink-100 text-pink-800 ring-pink-200', 'titik' => 'bg-pink-500', 'garis' => 'bg-pink-500'],
        'rose'    => ['pita' => 'bg-rose-100 text-rose-800 ring-rose-200', 'titik' => 'bg-rose-500', 'garis' => 'bg-rose-500'],
        'orange'  => ['pita' => 'bg-orange-100 text-orange-800 ring-orange-200', 'titik' => 'bg-orange-500', 'garis' => 'bg-orange-500'],
        'amber'   => ['pita' => 'bg-amber-100 text-amber-800 ring-amber-200', 'titik' => 'bg-amber-500', 'garis' => 'bg-amber-500'],
        'lime'    => ['pita' => 'bg-lime-100 text-lime-800 ring-lime-200', 'titik' => 'bg-lime-500', 'garis' => 'bg-lime-500'],
        'slate'   => ['pita' => 'bg-slate-200 text-slate-700 ring-slate-300', 'titik' => 'bg-slate-500', 'garis' => 'bg-slate-500'],
    ];

    /** Warna untuk kegiatan "Tanpa jenis" */
    public const WARNA_KOSONG = 'slate';

    public function kegiatan(): HasMany
    {
        return $this->hasMany(KalenderPendidikan::class, 'jenis_id');
    }

    /** Kelas Tailwind satu warna (warna tidak dikenal / kosong = abu-abu) */
    public static function kelas(?string $warna): array
    {
        return self::KELAS_WARNA[$warna] ?? self::KELAS_WARNA[self::WARNA_KOSONG];
    }

    /** Pilihan warna untuk jendela Jenis & Warna: [['kunci', 'label', 'kelas'], ...] */
    public static function pilihanWarna(): array
    {
        return collect(self::WARNA)->map(fn ($label, $kunci) => ['kunci' => $kunci, 'label' => __($label), 'kelas' => self::KELAS_WARNA[$kunci]])->values()->all();
    }

    /** Semua jenis untuk tampilan: [['id', 'nama', 'warna', 'kelas'], ...] urut */
    public static function daftar(): array
    {
        if (! Schema::hasTable('jenis_kalender')) {
            return [];
        }

        return static::orderBy('urutan')->orderBy('id')->get()
            ->map(fn ($j) => ['id' => $j->id, 'nama' => $j->nama, 'warna' => $j->warna, 'kelas' => self::kelas($j->warna)])
            ->all();
    }
}
