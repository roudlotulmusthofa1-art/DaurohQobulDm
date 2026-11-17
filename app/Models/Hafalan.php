<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satu setoran hafalan santri untuk satu kitab (Asatid > Hafalan, Admin > Akademik > Hafalan).
 * Pengaturan halaman ini (per sesi, tabel pengaturan):
 *   hafalan_kitab = json id kitab yang wajib dihafal (dipilih dari Kurikulum & Kitab)
 *   hafalan_nilai = json daftar pilihan nilai yang ditulis admin (kosong = NILAI_BAWAAN)
 */
class Hafalan extends Model
{
    /** Contoh pilihan nilai bila admin belum mengatur */
    public const NILAI_BAWAAN = ['Mumtaz', 'Jayyid Jiddan', 'Jayyid', 'Maqbul', 'Rasib'];

    protected $table = 'hafalan';

    protected $fillable = [
        'pendaftaran_siswa_id', 'kitab_id', 'tanggal', 'halaman_dari', 'halaman_sampai', 'ragu', 'salah', 'nilai', 'dinilai_oleh',
    ];

    protected $casts = [
        'tanggal'        => 'date',
        'halaman_dari'   => 'integer',
        'halaman_sampai' => 'integer',
        'ragu'           => 'integer',
        'salah'          => 'integer',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(PendaftaranSiswa::class, 'pendaftaran_siswa_id');
    }

    public function kitab(): BelongsTo
    {
        return $this->belongsTo(Kitab::class);
    }

    public function penilai(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dinilai_oleh');
    }

    /** Id kitab yang wajib dihafal (urutan sesuai pilihan admin) */
    public static function idKitab(): array
    {
        $ids = json_decode((string) Pengaturan::ambil('hafalan_kitab'), true);

        return is_array($ids) ? array_values(array_map('intval', $ids)) : [];
    }

    /** Kitab yang wajib dihafal: [{id, nama, arab}] */
    public static function daftarKitab(): array
    {
        $ids = self::idKitab();
        if (! $ids) {
            return [];
        }
        $kitab = Kitab::whereIn('id', $ids)->get(['id', 'nama', 'nama_arab'])->keyBy('id');

        return collect($ids)->filter(fn ($id) => $kitab->has($id))
            ->map(fn ($id) => ['id' => $id, 'nama' => $kitab[$id]->nama, 'arab' => $kitab[$id]->nama_arab])
            ->values()->all();
    }

    /** Pilihan nilai yang ditulis admin (atau contoh bawaan) */
    public static function pilihanNilai(): array
    {
        $isi = json_decode((string) Pengaturan::ambil('hafalan_nilai'), true);

        return is_array($isi) && $isi ? array_values($isi) : self::NILAI_BAWAAN;
    }

    /** Jam sekarang WIB apa adanya (server memakai UTC) */
    public static function sekarang()
    {
        return now('Asia/Jakarta');
    }
}
