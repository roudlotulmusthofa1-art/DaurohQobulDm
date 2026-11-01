<?php

namespace App\Models;

use App\Models\Concerns\IsMasterData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class Kamar extends Model
{
    use IsMasterData;

    protected $table = 'kamar';

    protected $fillable = ['nama', 'kode', 'gedung', 'jenis', 'kapasitas', 'deskripsi', 'urutan', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'urutan' => 'integer',
        'kapasitas' => 'integer',
    ];

    /** Santri yang menempati kamar ini (kolom pendaftaran_siswas.kamar_id). */
    public function santri(): HasMany
    {
        return $this->hasMany(PendaftaranSiswa::class, 'kamar_id');
    }

    /** Dihitung di Master Data, dan kamar yang masih berisi santri tidak bisa dihapus. */
    public static function countRelation(): ?string
    {
        return Schema::hasColumn('pendaftaran_siswas', 'kamar_id') ? 'santri' : null;
    }

    /** Teks pilihan, mis. "Kamar Abu Bakar (3/12)". Butuh santri_count (withCount('santri')). */
    public function getLabelIsiAttribute(): string
    {
        $isi = $this->santri_count ?? $this->santri()->count();

        return $this->kapasitas > 0 ? "{$this->nama} ({$isi}/{$this->kapasitas})" : $this->nama;
    }

    /** Apakah kamar sudah penuh (kapasitas 0 = tanpa batas). */
    public function penuh(?int $kecualiSantriId = null): bool
    {
        if ($this->kapasitas <= 0) {
            return false;
        }

        $isi = $this->santri()->when($kecualiSantriId, fn ($q) => $q->whereKeyNot($kecualiSantriId))->count();

        return $isi >= $this->kapasitas;
    }
}
