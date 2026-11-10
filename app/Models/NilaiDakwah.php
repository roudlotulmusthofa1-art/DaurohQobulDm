<?php

namespace App\Models;

use App\Support\DataCatatan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Penilaian Dakwah: satu nilai Khitoba' atau Khurujud Dakwah untuk satu santri.
 * "nilai" & "waktu" berupa tulisan (disalin dari PilihanDakwah saat menilai).
 */
class NilaiDakwah extends Model
{
    public const KHITOBAH = 'khitobah';
    public const KHURUJ = 'khuruj';

    /** Jenis => label. Urutan ini dipakai di semua tampilan. */
    public const JENIS = [
        self::KHITOBAH => "Khitoba'",
        self::KHURUJ   => 'Khurujud Dakwah',
    ];

    protected $table = 'nilai_dakwah';

    protected $fillable = ['jenis', 'pendaftaran_siswa_id', 'waktu', 'tanggal', 'nilai', 'catatan', 'dinilai_oleh'];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(PendaftaranSiswa::class, 'pendaftaran_siswa_id');
    }

    /** Admin yang menilai */
    public function penilai(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dinilai_oleh');
    }

    /** Satu baris untuk Vue */
    public function baris(): array
    {
        return DataCatatan::siswa($this->siswa) + [
            'id'      => $this->id,
            'jenis'   => $this->jenis,
            'label'   => __(self::JENIS[$this->jenis] ?? $this->jenis),
            'waktu'   => $this->waktu,
            'tanggal' => $this->tanggal->toDateString(),
            'hari'    => $this->tanggal->locale('id')->translatedFormat('l'),
            'nilai'   => $this->nilai,
            'catatan' => $this->catatan,
            'oleh'    => $this->penilai?->name,
        ];
    }

    /** Muat relasi yang dipakai baris() */
    public function scopeLengkap($q)
    {
        return $q->with(['siswa.kelasMaster', 'penilai:id,name']);
    }
}
