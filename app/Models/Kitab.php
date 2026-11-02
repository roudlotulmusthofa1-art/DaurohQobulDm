<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Kurikulum: kitab per bidang ilmu, untuk satu tahun ajaran, diajarkan pada bulan ke berapa saja.
 * Kelas dipilih di Jadwal Mengajar.
 */
class Kitab extends Model
{
    protected $table = 'kitab';

    protected $fillable = ['nama', 'nama_arab', 'tahun_ajaran_id', 'bidang_ilmu_id', 'bulan_ke', 'frekuensi', 'deskripsi', 'is_active'];

    protected $casts = [
        'bulan_ke' => 'array',
        'frekuensi' => 'integer',
        'is_active' => 'boolean',
    ];

    public function bidangIlmu(): BelongsTo
    {
        return $this->belongsTo(BidangIlmu::class);
    }

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function jadwal(): HasMany
    {
        return $this->hasMany(JadwalMengajar::class);
    }

    /** Kitab yang diajarkan pada bulan ajaran ini (tahun ajarannya sama, dan bulan ke-nya dipilih / semua bulan). */
    public function scopeUntukBulan(Builder $q, BulanAjaran $bulan): Builder
    {
        return $q->where(fn ($q) => $q->where('tahun_ajaran_id', $bulan->tahun_ajaran_id)->orWhereNull('tahun_ajaran_id'))
            ->where(fn ($q) => $q->whereNull('bulan_ke')->orWhereJsonLength('bulan_ke', 0)->orWhereJsonContains('bulan_ke', (int) $bulan->urutan));
    }

    /** Nama-nama bulan yang dipilih, mis. "1 · Rabi'ul Akhir 1448". Kosong = semua bulan. */
    public function getBulanLabelAttribute(): array
    {
        $bulan = $this->tahunAjaran?->bulan?->keyBy('urutan') ?? collect();

        return collect($this->bulan_ke ?? [])->map(fn ($n) => $n . ($bulan[$n] ?? null ? ' · ' . $bulan[$n]->nama : ''))->all();
    }
}
