<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Satu slot jadwal: bulan ajaran + hari + jam pelajaran + kelas + kitab + ustadz. */
class JadwalMengajar extends Model
{
    protected $table = 'jadwal_mengajar';

    protected $fillable = ['bulan_ajaran_id', 'hari', 'waktu_pelajaran_id', 'kelas_id', 'kitab_id', 'user_id', 'catatan'];

    protected $casts = ['hari' => 'integer'];

    /** 1 = Senin ... 7 = Ahad (sama dengan Carbon::dayOfWeekIso) */
    public const HARI = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Ahad'];

    public function bulanAjaran(): BelongsTo
    {
        return $this->belongsTo(BulanAjaran::class);
    }

    public function waktu(): BelongsTo
    {
        return $this->belongsTo(WaktuPelajaran::class, 'waktu_pelajaran_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function kitab(): BelongsTo
    {
        return $this->belongsTo(Kitab::class);
    }

    public function ustadz(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getNamaHariAttribute(): string
    {
        return self::HARI[$this->hari] ?? '-';
    }
}
