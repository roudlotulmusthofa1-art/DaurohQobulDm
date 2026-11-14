<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Pengaturan Ujian (tulis) dari admin: Penilaian > Kelas & Pelajaran > Ujian > tombol Pengaturan Ujian.
 * Satu ujian = bulan ajaran (boleh 1, 2, 3 bulan atau lebih; tabel ujian_bulan) + satu fan + satu kitab atau lebih + nilai maksimal.
 * Kolom "nama" diisi otomatis dari nama bulannya (mis. "Muharram, Rabi'ul Akhir"). Ujian lama tanpa bulan memakai nama lamanya.
 * Asatid membuat soal (SoalUjian) dan menilai santri (NilaiUjian) per kitab, hanya untuk ujian yang aktif.
 * Cara pembuatan soal diatur admin (cara_soal): per kitab, seluruh fan, atau kelompok kitab (kelompok_soal).
 * Asatid hanya bisa memilih salah satu kelompok dari kelompok() saat membuat soal.
 */
class Ujian extends Model
{
    public const MAKS_NILAI = 1000;

    /** Cara pembuatan soal => label */
    public const CARA_SOAL = [
        'per_kitab'   => 'Per kitab',
        'seluruh_fan' => 'Seluruh fan',
        'kelompok'    => 'Atur kelompok',
    ];

    protected $table = 'ujian';

    protected $fillable = ['nama', 'bidang_ilmu_id', 'nilai_maks', 'cara_soal', 'kelompok_soal', 'is_active'];

    protected $casts = [
        'is_active'     => 'boolean',
        'kelompok_soal' => 'array',
    ];

    public function bidangIlmu(): BelongsTo
    {
        return $this->belongsTo(BidangIlmu::class);
    }

    /** Bulan ajaran yang dicakup ujian ini */
    public function bulan(): BelongsToMany
    {
        return $this->belongsToMany(BulanAjaran::class, 'ujian_bulan');
    }

    /** Nama ujian dari bulan-bulannya, urut tahun ajaran & urutan bulan: "Muharram, Rabi'ul Akhir" */
    public static function namaDariBulan(array $bulanIds): ?string
    {
        return BulanAjaran::whereIn('id', $bulanIds)->orderBy('tahun_ajaran_id')->orderBy('urutan')->orderBy('id')
            ->pluck('nama')->implode(', ') ?: null;
    }

    public function kitab(): BelongsToMany
    {
        return $this->belongsToMany(Kitab::class, 'ujian_kitab');
    }

    public function soal(): HasMany
    {
        return $this->hasMany(SoalUjian::class);
    }

    public function nilai(): HasMany
    {
        return $this->hasMany(NilaiUjian::class);
    }

    /** Judul untuk tampilan: "Nama ujian · Fan" (nama boleh kosong) */
    public function judul(): string
    {
        return collect([$this->nama, $this->bidangIlmu?->nama])->filter()->implode(' · ') ?: __('Ujian');
    }

    /**
     * Kelompok soal yang boleh dipilih asatid, sesuai pengaturan admin:
     * [['kitab_ids' => [2, 3], 'nama' => 'jurmiyyah + متممة'], ...]. Kitab yang sudah dilepas dari ujian diabaikan.
     */
    public function kelompok(): array
    {
        $kitab = $this->kitab->sortBy('nama')->keyBy('id');
        $ids = $kitab->keys()->map(fn ($id) => (int) $id)->all();
        if (! $ids) {
            return [];
        }

        $grup = match ($this->cara_soal) {
            'seluruh_fan' => [$ids],
            'kelompok'    => collect($this->kelompok_soal ?? [])
                ->map(fn ($g) => array_values(array_intersect($ids, array_map('intval', (array) $g))))
                ->filter()->values()->all(),
            default       => array_map(fn ($id) => [$id], $ids),
        };

        return array_map(fn ($g) => [
            'kitab_ids' => $g,
            'kunci'     => self::kunci($g),
            'seluruh'   => count($g) === count($ids) && count($ids) > 1,
            'nama'      => count($g) === count($ids) && count($ids) > 1
                ? __('Seluruh fan :fan', ['fan' => $this->bidangIlmu?->nama ?? ''])
                : implode(' + ', array_map(fn ($id) => $kitab[$id]->nama, $g)),
        ], $grup);
    }

    /** Kunci kelompok: id kitab diurutkan lalu digabung "-", mis. [3, 2] -> "2-3" (dipakai soal & nilai) */
    public static function kunci(array $kitabIds): string
    {
        $ids = array_map('intval', $kitabIds);
        sort($ids);

        return implode('-', $ids);
    }

    /** Kelompok yang kitabnya sama persis dengan $kitabIds (urutan tidak penting), atau null */
    public function cariKelompok(array $kitabIds): ?array
    {
        $cari = array_map('intval', $kitabIds);
        sort($cari);
        foreach ($this->kelompok() as $g) {
            $ids = $g['kitab_ids'];
            sort($ids);
            if ($ids === $cari) {
                return $g;
            }
        }

        return null;
    }

    /** Semua ujian (atau yang aktif saja), terbaru dulu */
    public static function daftar(bool $hanyaAktif = false): array
    {
        return self::with(['bidangIlmu:id,nama', 'kitab:kitab.id,kitab.nama,kitab.nama_arab', 'bulan:bulan_ajaran.id'])->withCount(['nilai', 'soal'])
            ->when($hanyaAktif, fn ($q) => $q->where('is_active', true))
            ->orderByDesc('id')
            ->get()
            ->map(fn (Ujian $u) => $u->baris())
            ->values()->all();
    }

    /** Satu baris untuk Vue */
    public function baris(): array
    {
        return [
            'id'             => $this->id,
            'nama'           => $this->nama,
            'judul'          => $this->judul(),
            'bidang_ilmu_id' => $this->bidang_ilmu_id,
            'fan'            => $this->bidangIlmu?->nama,
            'kitab'          => $this->kitab->sortBy('nama')->map(fn ($k) => ['id' => $k->id, 'nama' => $k->nama, 'arab' => $k->nama_arab])->values()->all(),
            'kitab_ids'      => $this->kitab->pluck('id')->all(),
            'bulan_ids'      => $this->bulan->pluck('id')->map(fn ($id) => (int) $id)->all(),   // kosong = ujian lama (nama bebas)
            'nilai_maks'     => (int) $this->nilai_maks,
            'cara_soal'      => $this->cara_soal ?: 'per_kitab',
            'kelompok_soal'  => $this->kelompok_soal ?? [],
            'kelompok'       => $this->kelompok(),   // pilihan soal untuk asatid
            'is_active'      => (bool) $this->is_active,
            'jumlah_nilai'   => (int) ($this->nilai_count ?? 0),
            'jumlah_soal'    => (int) ($this->soal_count ?? 0),
        ];
    }
}
