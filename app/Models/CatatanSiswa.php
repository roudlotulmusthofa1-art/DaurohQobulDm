<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Penilaian > Catatan: satu catatan Pencapaian / Pelanggaran untuk satu santri, ditulis asatid.
 * Data untuk tampilan (termasuk Terlambat/Alfa dari absensi) disiapkan di App\Support\DataCatatan.
 */
class CatatanSiswa extends Model
{
    public const PENCAPAIAN = 'pencapaian';
    public const PELANGGARAN = 'pelanggaran';

    /** Jenis => label. Urutan ini dipakai di semua tampilan. */
    public const JENIS = [
        self::PENCAPAIAN  => 'Pencapaian',
        self::PELANGGARAN => 'Pelanggaran',
    ];

    protected $table = 'catatan_siswa';

    protected $fillable = ['pendaftaran_siswa_id', 'jenis', 'catatan', 'tanggal', 'jam', 'dicatat_oleh'];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(PendaftaranSiswa::class, 'pendaftaran_siswa_id');
    }

    /** Asatid / admin yang menulis catatan */
    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }
}
