<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * Absensi Kegiatan berulang (seperti alarm HP): kegiatan + hari + jam + saklar ON/OFF.
 * Pada hari yang dipilih, absensi kegiatan dibuat OTOMATIS dan langsung aktif di dasbor semua asatid
 * (buatHariIni, dipanggil dari Absensi::tutupYangLewat saat halaman absensi admin / asatid dibuka).
 * Bila halaman pertama kali dibuka setelah batas alfa, hari itu dilewati (supaya santri tidak otomatis Alfa).
 */
class JadwalKegiatan extends Model
{
    /** 1 = Senin ... 7 = Ahad (Carbon dayOfWeekIso) => singkatan tombol */
    public const HARI = [1 => 'S', 2 => 'S', 3 => 'R', 4 => 'K', 5 => 'J', 6 => 'S', 7 => 'A'];
    public const NAMA_HARI = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Ahad'];

    protected $table = 'jadwal_kegiatan';

    protected $fillable = [
        'jenis_kegiatan_id', 'hari', 'jam_mulai', 'batas_terlambat', 'batas_alfa', 'metode', 'nyala', 'terakhir_dibuat', 'user_id',
    ];

    protected $casts = [
        'hari'            => 'array',
        'nyala'           => 'boolean',
        'terakhir_dibuat' => 'date',
    ];

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(JenisKegiatan::class, 'jenis_kegiatan_id');
    }

    /** "Setiap hari", "Senin–Jumat", atau "Senin, Rabu, Jumat" */
    public function getLabelHariAttribute(): string
    {
        $hari = collect($this->hari ?? [])->map(fn ($h) => (int) $h)->sort()->values()->all();

        return match (true) {
            count($hari) === 7 => __('Setiap hari'),
            $hari === [1, 2, 3, 4, 5] => __('Senin–Jumat'),
            $hari === [6, 7] => __('Sabtu & Ahad'),
            default => collect($hari)->map(fn ($h) => __(self::NAMA_HARI[$h]))->join(', '),
        };
    }

    /** Buat absensi kegiatan hari ini dari semua jadwal yang menyala & harinya cocok (paling banyak sekali per hari). */
    public static function buatHariIni(): void
    {
        $sekarang = now('Asia/Jakarta');
        $tanggal = $sekarang->toDateString();
        $jam = $sekarang->format('H:i');

        try {
            $jadwal = static::where('nyala', true)
                ->where(fn ($q) => $q->whereNull('terakhir_dibuat')->orWhereDate('terakhir_dibuat', '<', $tanggal))
                ->get();
        } catch (QueryException $e) {
            return; // sesi lama yang belum menjalankan sesi:migrate
        }

        foreach ($jadwal as $j) {
            if (! in_array($sekarang->dayOfWeekIso, array_map('intval', $j->hari ?? []), true)) {
                continue;
            }
            DB::transaction(function () use ($j, $tanggal, $jam) {
                // Kunci baris jadwal supaya dua halaman yang dibuka bersamaan tidak membuat dua absensi
                $j = static::lockForUpdate()->find($j->id);
                if (! $j || ($j->terakhir_dibuat && $j->terakhir_dibuat->toDateString() >= $tanggal)) {
                    return;
                }
                $j->update(['terakhir_dibuat' => $tanggal]);
                // Sudah lewat batas alfa saat pertama kali dicek -> hari ini dilewati
                if ($jam >= substr((string) $j->batas_alfa, 0, 5)) {
                    return;
                }
                Absensi::create([
                    'jenis'              => Absensi::KEGIATAN,
                    'user_id'            => $j->user_id,
                    'dari_admin'         => true,
                    'aktif'              => true,
                    'jenis_kegiatan_id'  => $j->jenis_kegiatan_id,
                    'jadwal_kegiatan_id' => $j->id,
                    'tanggal'            => $tanggal,
                    'tanggal_hijriyah'   => Absensi::hijriyah($tanggal),
                    'jam_mulai'          => substr((string) $j->jam_mulai, 0, 5),
                    'batas_terlambat'    => substr((string) $j->batas_terlambat, 0, 5),
                    'batas_alfa'         => substr((string) $j->batas_alfa, 0, 5),
                    'metode'             => $j->metode,
                ]);
            });
        }
    }
}
