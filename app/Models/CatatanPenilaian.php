<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Dasar untuk laporan Pelanggaran & Pencapaian siswa (bentuknya sama persis).
 * Anak kelas cukup mengisi $table, KOLOM_JENIS dan MODEL_JENIS.
 * Daftar jenis laporan + labelnya ada di App\Support\JenisPenilaian.
 */
abstract class CatatanPenilaian extends Model
{
    /** Kolom jenis, mis. "jenis_pelanggaran_id" */
    public const KOLOM_JENIS = '';

    /** Model Master Data jenisnya, mis. JenisPelanggaran::class */
    public const MODEL_JENIS = '';

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function getFillable(): array
    {
        return ['pendaftaran_siswa_id', static::KOLOM_JENIS, 'tanggal', 'jam', 'catatan', 'dicatat_oleh'];
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(PendaftaranSiswa::class, 'pendaftaran_siswa_id');
    }

    public function jenis(): BelongsTo
    {
        return $this->belongsTo(static::MODEL_JENIS, static::KOLOM_JENIS);
    }

    /** Asatid / admin yang melaporkan */
    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }
}
