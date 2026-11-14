<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Pengaturan Ujian Lisan dari admin (Penilaian > Kelas & Pelajaran > Ujian Lisan > tombol Pengaturan).
 * Satu baris = satu ujian: fan (bidang ilmu) + kitab (boleh kosong) + batas nilai tiap pertanyaan.
 * Jumlah pertanyaan = banyaknya isi "nilai_pertanyaan"; "total_maks" dihitung otomatis.
 * Asatid hanya melihat ujian yang aktif.
 */
class UjianLisan extends Model
{
    /** Batas wajar supaya form tetap rapi di HP */
    public const MAKS_PERTANYAAN = 50;
    public const MAKS_NILAI = 1000;

    protected $table = 'ujian_lisan';

    protected $fillable = ['bidang_ilmu_id', 'kitab_id', 'nilai_pertanyaan', 'is_active'];

    protected $casts = [
        'nilai_pertanyaan' => 'array',
        'is_active'        => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (UjianLisan $u) {
            $u->nilai_pertanyaan = array_values(array_map(fn ($n) => max(1, min(self::MAKS_NILAI, (int) $n)), $u->nilai_pertanyaan ?? []));
            $u->total_maks = array_sum($u->nilai_pertanyaan);
        });
    }

    public function bidangIlmu(): BelongsTo
    {
        return $this->belongsTo(BidangIlmu::class);
    }

    public function kitab(): BelongsTo
    {
        return $this->belongsTo(Kitab::class);
    }

    public function nilai(): HasMany
    {
        return $this->hasMany(NilaiUjianLisan::class);
    }

    /** Ujian yang aktif, urut nama fan lalu kitab (untuk pilihan asatid & admin) */
    public static function daftar(bool $hanyaAktif = false): array
    {
        return self::with(['bidangIlmu:id,nama', 'kitab:id,nama,nama_arab'])->withCount('nilai')
            ->when($hanyaAktif, fn ($q) => $q->where('is_active', true))
            ->get()
            ->map(fn (UjianLisan $u) => $u->baris())
            ->sortBy(fn ($b) => mb_strtolower(($b['fan'] ?? '~') . ' ' . ($b['kitab'] ?? '')))
            ->values()->all();
    }

    /** Satu baris untuk Vue */
    public function baris(): array
    {
        return [
            'id'             => $this->id,
            'bidang_ilmu_id' => $this->bidang_ilmu_id,
            'fan'            => $this->bidangIlmu?->nama,
            'kitab_id'       => $this->kitab_id,
            'kitab'          => $this->kitab?->nama,
            'kitab_arab'     => $this->kitab?->nama_arab,
            'maks'           => array_map('intval', $this->nilai_pertanyaan ?? []),
            'total_maks'     => (int) $this->total_maks,
            'is_active'      => (bool) $this->is_active,
            'jumlah_nilai'   => (int) ($this->nilai_count ?? 0),
        ];
    }
}
