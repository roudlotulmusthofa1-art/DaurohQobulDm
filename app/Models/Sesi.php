<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Sesi = angkatan/gelombang dauroh. Disimpan di database utama (koneksi "pusat").
 * Tiap sesi punya database & folder file sendiri; sesi "utama" memakai database utama.
 * Perpindahan database diatur oleh App\Support\SesiAplikasi.
 */
class Sesi extends Model
{
    protected $connection = 'pusat';

    protected $table = 'sesi';

    protected $fillable = ['nama', 'kode', 'database', 'tanggal_mulai', 'tanggal_selesai', 'keterangan', 'is_active'];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'is_active' => 'boolean',
    ];

    /** Sesi utama = database utama (data lama sebelum fitur sesi). Tidak bisa dihapus. */
    public function getUtamaAttribute(): bool
    {
        return $this->database === null;
    }

    /** Nama database yang dipakai sesi ini */
    public function getNamaDatabaseAttribute(): string
    {
        return $this->database ?? (string) config('database.connections.pusat.database');
    }

    /** Folder file sesi ini di dalam storage/app/public ("" = folder utama) */
    public function getFolderAttribute(): string
    {
        return $this->utama ? '' : 'sesi/' . $this->kode;
    }

    /** Nama koneksi database Laravel untuk sesi ini (dibuat otomatis bila belum ada) */
    public function koneksi(): string
    {
        return \App\Support\SesiAplikasi::koneksiSesi($this);
    }

    public function getPeriodeAttribute(): ?string
    {
        if (! $this->tanggal_mulai) {
            return null;
        }

        return $this->tanggal_mulai->translatedFormat('j M Y') . ($this->tanggal_selesai ? ' – ' . $this->tanggal_selesai->translatedFormat('j M Y') : '');
    }
}
