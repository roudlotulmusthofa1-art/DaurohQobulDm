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
        'diberitahu_pada', 'diberitahu_lewat', 'diberitahu_keputusan', // Laporan Kelulusan (pesan WhatsApp)
    ];

    protected $casts = [
        'isian'           => 'array',
        'dikirim_pada'    => 'datetime',
        'diputuskan_pada' => 'datetime',
        'diberitahu_pada' => 'datetime',
    ];

    /** Sudah diberitahu hasil keputusan yang SEKARANG (keputusan diubah sesudahnya = belum) */
    public function sudahDiberitahu(): bool
    {
        return $this->diberitahu_pada !== null && $this->diberitahu_keputusan === $this->keputusan;
    }

    /**
     * Hasil Istimaroh yang sudah diumumkan ke santri ini (dasbor siswa > Pengumuman Kelulusan).
     * Diumumkan = sudah diputuskan Lajnah DAN sudah diberitahu lewat Laporan Kelulusan (Fonnte / WA).
     * null = belum diumumkan (atau tabel belum ada di sesi ini).
     */
    public static function pengumumanUntuk(?PendaftaranSiswa $s): ?self
    {
        if (! $s) {
            return null;
        }
        try {
            $m = static::with('siswa.kelasMaster:id,nama')->where('pendaftaran_siswa_id', $s->id)
                ->where('status', self::LAJNAH)->whereIn('keputusan', [self::LULUS, self::TIDAK])->first();
        } catch (\Illuminate\Database\QueryException $e) {
            return null; // sesi lama yang belum menjalankan sesi:migrate
        }

        return $m?->sudahDiberitahu() ? $m : null;
    }

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
