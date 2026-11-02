<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Status satu peserta di satu sesi absensi. */
class AbsensiDetail extends Model
{
    protected $table = 'absensi_detail';

    protected $fillable = ['absensi_id', 'pendaftaran_siswa_id', 'status', 'catatan', 'waktu'];

    public function absensi(): BelongsTo
    {
        return $this->belongsTo(Absensi::class);
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(PendaftaranSiswa::class, 'pendaftaran_siswa_id');
    }
}
