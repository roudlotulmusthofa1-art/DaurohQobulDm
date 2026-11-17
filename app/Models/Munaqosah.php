<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Akademik > Munaqosah: hasil musyawarah asatid untuk satu santri.
 * Alur: Musyrifin Dauroh mengisi (Nilai Ilmu, Suluk, Catatan Mandub) -> Simpan -> status "lajnah"
 *       -> Lajnah Qobul Darul Musthofa memutuskan Lulus/Diterima atau Tidak (+ catatan khusus).
 * Selama belum diputuskan, Lajnah boleh mengembalikan ke Musyrifin.
 */
class Munaqosah extends Model
{
    public const MUSYRIFIN = 'musyrifin';
    public const LAJNAH = 'lajnah';

    public const LULUS = 'lulus';
    public const TIDAK = 'tidak';

    /** Keputusan => label */
    public const KEPUTUSAN = [
        self::LULUS => 'Lulus / Diterima',
        self::TIDAK => 'Tidak',
    ];

    protected $table = 'munaqosah';

    protected $fillable = [
        'pendaftaran_siswa_id', 'isian', 'status', 'disimpan_oleh', 'dikirim_pada',
        'keputusan', 'catatan_lajnah', 'diputuskan_oleh', 'diputuskan_pada',
    ];

    protected $casts = [
        'isian'           => 'array',
        'dikirim_pada'    => 'datetime',
        'diputuskan_pada' => 'datetime',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(PendaftaranSiswa::class, 'pendaftaran_siswa_id');
    }

    public function penyimpan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disimpan_oleh');
    }

    public function pemutus(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diputuskan_oleh');
    }

    /** Jam sekarang WIB apa adanya (server memakai UTC) */
    public static function sekarang()
    {
        return now('Asia/Jakarta');
    }
}
