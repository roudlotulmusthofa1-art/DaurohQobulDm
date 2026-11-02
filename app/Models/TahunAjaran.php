<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/** Tahun ajaran, mis. 1448/1449. Dibagi menjadi beberapa bulan ajaran. Hanya satu yang aktif. */
class TahunAjaran extends Model
{
    protected $table = 'tahun_ajaran';

    protected $fillable = ['nama', 'nama_masehi', 'tanggal_mulai', 'tanggal_selesai', 'is_active', 'keterangan'];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'is_active' => 'boolean',
    ];

    public function bulan(): HasMany
    {
        return $this->hasMany(BulanAjaran::class)->orderBy('urutan')->orderBy('id');
    }

    public static function aktif(): ?self
    {
        return static::where('is_active', true)->first();
    }

    /** Jadikan satu-satunya tahun ajaran aktif. Bulan aktif dipindah ke bulan pertama tahun ini bila perlu. */
    public function aktifkan(): void
    {
        DB::transaction(function () {
            static::whereKeyNot($this->id)->update(['is_active' => false]);
            $this->update(['is_active' => true]);

            $bulanAktif = BulanAjaran::aktif();
            if (! $bulanAktif || $bulanAktif->tahun_ajaran_id !== $this->id) {
                $this->bulan()->first()?->aktifkan();
            }
        });
    }
}
