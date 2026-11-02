<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/** Bulan ajaran (pengganti semester), mis. "Muharram 1448" = bulan ke-1 tahun ajaran 1448/1449. */
class BulanAjaran extends Model
{
    protected $table = 'bulan_ajaran';

    protected $fillable = ['tahun_ajaran_id', 'nama', 'urutan', 'tanggal_mulai', 'tanggal_selesai', 'is_active'];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'is_active' => 'boolean',
        'urutan' => 'integer',
    ];

    /** Nama bulan Hijriyah, untuk pilihan cepat di form */
    public const NAMA_HIJRIYAH = [
        'Muharram', 'Shafar', "Rabi'ul Awal", "Rabi'ul Akhir", 'Jumadil Ula', 'Jumadil Akhirah',
        'Rajab', "Sya'ban", 'Ramadhan', 'Syawwal', "Dzulqa'dah", 'Dzulhijjah',
    ];

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function jadwal(): HasMany
    {
        return $this->hasMany(JadwalMengajar::class);
    }

    public static function aktif(): ?self
    {
        return static::with('tahunAjaran')->where('is_active', true)->first();
    }

    /** Jadikan satu-satunya bulan aktif (tahun ajarannya ikut aktif). */
    public function aktifkan(): void
    {
        DB::transaction(function () {
            static::whereKeyNot($this->id)->update(['is_active' => false]);
            $this->update(['is_active' => true]);
            TahunAjaran::whereKeyNot($this->tahun_ajaran_id)->update(['is_active' => false]);
            TahunAjaran::whereKey($this->tahun_ajaran_id)->update(['is_active' => true]);
        });
    }

    /** Mis. "Bulan ke-1 · Muharram 1448 · TA 1448/1449" */
    public function getLabelAttribute(): string
    {
        return "Bulan ke-{$this->urutan} · {$this->nama}" . ($this->tahunAjaran ? " · TA {$this->tahunAjaran->nama}" : '');
    }

    /** Periode tanggal Masehi + Hijriyah, mis. "11 Sep – 10 Okt 2026" */
    public function getPeriodeAttribute(): ?string
    {
        if (! $this->tanggal_mulai || ! $this->tanggal_selesai) {
            return null;
        }

        return $this->tanggal_mulai->locale('id')->translatedFormat('d M Y') . ' – ' . $this->tanggal_selesai->locale('id')->translatedFormat('d M Y');
    }
}
