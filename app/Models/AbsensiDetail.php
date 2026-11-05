<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Status satu peserta di satu sesi absensi. */
class AbsensiDetail extends Model
{
    protected $table = 'absensi_detail';

    protected $fillable = ['absensi_id', 'pendaftaran_siswa_id', 'status', 'catatan', 'waktu', 'dicatat_oleh'];

    public function absensi(): BelongsTo
    {
        return $this->belongsTo(Absensi::class);
    }

    /** Asatid / admin yang mencatat peserta ini (absensi kegiatan bersama) */
    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(PendaftaranSiswa::class, 'pendaftaran_siswa_id');
    }
}
