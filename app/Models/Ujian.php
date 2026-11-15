<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Pengaturan Ujian (tulis) dari admin: Penilaian > Kelas & Pelajaran > Ujian > tombol Pengaturan Ujian.
 * Satu ujian = bulan ajaran (boleh 1, 2, 3 bulan atau lebih; tabel ujian_bulan) + kitab dari SATU ATAU BANYAK fan + nilai maksimal.
 * Fan tiap kitab diambil dari Kurikulum (kitab.bidang_ilmu_id). Kolom ujian.bidang_ilmu_id hanya terisi kalau semua kitabnya satu fan.
 * Kolom "nama" ditulis sendiri oleh admin (mis. "Ujian Tengah Dauroh"). Di Pengaturan Ujian, fan & kitab yang bisa dipilih
 * hanya yang diajarkan di bulan yang dicentang (Kurikulum: tahun ajaran + bulan ke- tiap kitab).
 * Asatid membuat soal (SoalUjian) dan menilai santri (NilaiUjian) per kitab, hanya untuk ujian yang aktif.
 * Cara pembuatan soal diatur admin (cara_soal): per kitab, per fan (kunci lama 'seluruh_fan'), atau kelompok kitab (kelompok_soal).
 * Asatid hanya bisa memilih salah satu kelompok dari kelompok() saat membuat soal.
 */
class Ujian extends Model
{
    public const MAKS_NILAI = 1000;

    /** Cara pembuatan soal => label */
    public const CARA_SOAL = [
        'per_kitab'   => 'Per kitab',
        'seluruh_fan' => 'Per fan',   // kunci lama tetap dipakai: satu soal untuk setiap fan
        'kelompok'    => 'Campuran per fan',   // sebagian fan satu soal, sebagian per kitab (dipilih per fan di Pengaturan Ujian)
    ];

    protected $table = 'ujian';

    /** Relasi yang dimuat setiap kali butuh kelompok() / baris(): kitab beserta fannya */
    public const MUAT = ['bidangIlmu:id,nama', 'kitab:kitab.id,kitab.nama,kitab.nama_arab,kitab.bidang_ilmu_id', 'kitab.bidangIlmu:id,nama'];

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

    /** Nama bulan-bulannya, urut tahun ajaran & urutan bulan: "Muharram, Rabi'ul Akhir" (dulu dipakai sebagai nama ujian otomatis) */
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

    /** Nama fan dari kitab-kitab ujian ini (urut abjad, tanpa dobel) */
    public function daftarFan(): array
    {
        return $this->kitab->map(fn ($k) => $k->bidangIlmu?->nama)->filter()->unique()->sort()->values()->all();
    }

    /** Judul untuk tampilan: "Nama ujian · Fan" kalau hanya satu fan, kalau banyak fan cukup nama ujian */
    public function judul(): string
    {
        $fan = $this->daftarFan();

        return collect([$this->nama, count($fan) === 1 ? $fan[0] : null])->filter()->implode(' · ') ?: __('Ujian');
    }

    /**
     * Kelompok soal yang boleh dipilih asatid, sesuai pengaturan admin:
     * [['kitab_ids' => [2, 3], 'nama' => 'jurmiyyah + متممة', 'fan' => 'Nahwu'], ...]. Kitab yang sudah dilepas dari ujian diabaikan.
     * Nama selalu nama kitabnya (juga untuk satu soal seluruh fan); "seluruh" = true kalau semua kitab fan itu ikut.
     * - per_kitab   : satu kelompok per kitab
     * - seluruh_fan : satu kelompok per FAN (semua kitab fan itu dalam satu soal)
     * - kelompok    : kelompok buatan admin (boleh menggabungkan kitab dari fan berbeda)
     */
    public function kelompok(): array
    {
        $kitab = $this->kitab->sortBy(fn ($k) => [$k->bidangIlmu?->nama ?? '', $k->nama])->keyBy('id');
        $ids = $kitab->keys()->map(fn ($id) => (int) $id)->all();
        if (! $ids) {
            return [];
        }
        $fanKitab = fn ($id) => $kitab[$id]->bidangIlmu?->nama;
        // Banyak kitab per fan: semua kitab fan itu ada di kelompok ini -> "Seluruh fan X"
        $perFan = $kitab->groupBy(fn ($k) => $k->bidang_ilmu_id ?? 0, true)->map(fn ($g) => $g->keys()->map(fn ($id) => (int) $id)->sort()->values()->all());

        $grup = match ($this->cara_soal) {
            'seluruh_fan' => $perFan->values()->all(),
            'kelompok'    => collect($this->kelompok_soal ?? [])
                ->map(fn ($g) => array_values(array_intersect($ids, array_map('intval', (array) $g))))
                ->filter()->values()->all(),
            default       => array_map(fn ($id) => [$id], $ids),
        };

        return array_map(function ($g) use ($kitab, $fanKitab, $perFan) {
            $fan = collect($g)->map($fanKitab)->filter()->unique()->values();
            $urut = $g;
            sort($urut);
            // Satu fan, lebih dari satu kitab, dan semua kitab fan itu ikut -> "Seluruh fan X"
            $seluruh = $fan->count() === 1 && count($g) > 1 && $perFan->contains(fn ($ids) => $ids === $urut);

            return [
                'kitab_ids' => $g,
                'kunci'     => self::kunci($g),
                'seluruh'   => $seluruh,
                'fan'       => $fan->implode(', '),
                // Selalu nama kitabnya, juga kalau satu soal untuk seluruh fan: "jurmiyyah + متممة" (disimpan di soal & nilai)
                'nama'      => implode(' + ', array_map(fn ($id) => $kitab[$id]->nama, $g)),
                // Untuk tampilan saja: fan di depan, "Nahwu: jurmiyyah + متممة"
                'label'     => ($fan->isNotEmpty() ? $fan->implode(', ') . ': ' : '') . implode(' + ', array_map(fn ($id) => $kitab[$id]->nama, $g)),
            ];
        }, $grup);
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
        return self::with([...self::MUAT, 'bulan:bulan_ajaran.id'])->withCount(['nilai', 'soal'])
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
            'fan'            => implode(', ', $this->daftarFan()),   // satu atau banyak fan
            'fans'           => $this->daftarFan(),
            'kitab'          => $this->kitab->sortBy('nama')->map(fn ($k) => ['id' => $k->id, 'nama' => $k->nama, 'arab' => $k->nama_arab, 'bidang_ilmu_id' => $k->bidang_ilmu_id, 'fan' => $k->bidangIlmu?->nama])->values()->all(),
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
