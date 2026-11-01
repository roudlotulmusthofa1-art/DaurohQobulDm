<?php

namespace App\Models;

use App\Models\Concerns\IsMasterData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class Kelas extends Model
{
    use IsMasterData;

    protected $table = 'kelas';

    protected $fillable = ['nama', 'kode', 'tingkat', 'deskripsi', 'urutan', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'urutan' => 'integer',
        'tingkat' => 'integer',
    ];

    /** Santri yang ditempatkan di kelas ini (kolom pendaftaran_siswas.kelas_id). */
    public function santri(): HasMany
    {
        return $this->hasMany(PendaftaranSiswa::class, 'kelas_id');
    }

    /** Dihitung di Master Data, dan kelas yang masih berisi santri tidak bisa dihapus. */
    public static function countRelation(): ?string
    {
        return Schema::hasColumn('pendaftaran_siswas', 'kelas_id') ? 'santri' : null;
    }
}
