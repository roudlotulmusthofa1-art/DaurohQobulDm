<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Kurikulum: kitab yang diajarkan, per bidang ilmu, di kelas tertentu, pada bulan ke tertentu. */
class Kitab extends Model
{
    protected $table = 'kitab';

    protected $fillable = ['nama', 'nama_arab', 'pengarang', 'bidang_ilmu_id', 'jenis', 'bulan_ke', 'frekuensi', 'deskripsi', 'is_active'];

    protected $casts = [
        'bulan_ke' => 'array',
        'frekuensi' => 'integer',
        'is_active' => 'boolean',
    ];

    public const JENIS = [
        'teori' => 'Teori/Kitab',
        'praktik' => 'Praktik',
    ];

    public function bidangIlmu(): BelongsTo
    {
        return $this->belongsTo(BidangIlmu::class);
    }

    public function kelas(): BelongsToMany
    {
        return $this->belongsToMany(Kelas::class, 'kitab_kelas');
    }

    public function jadwal(): HasMany
    {
        return $this->hasMany(JadwalMengajar::class);
    }
}
