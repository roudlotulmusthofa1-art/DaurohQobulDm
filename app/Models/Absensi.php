<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use IntlDateFormatter;

/**
 * Satu sesi absensi peserta (lihat migrasi create_absensi_tables).
 * Status tiap peserta ada di AbsensiDetail.
 */
class Absensi extends Model
{
    public const KELAS = 'kelas';
    public const KEGIATAN = 'kegiatan';

    /** Status => [label, label pendek, warna]. Urutan ini dipakai di semua tampilan. */
    public const STATUS = [
        'hadir'     => ['Hadir', 'H', 'emerald'],
        'terlambat' => ['Terlambat', 'T', 'amber'],
        'sakit'     => ['Sakit', 'S', 'sky'],
        'izin'      => ['Izin', 'I', 'violet'],
        'alfa'      => ['Alfa', 'A', 'rose'],
    ];

    protected $table = 'absensi';

    protected $fillable = [
        'jenis', 'user_id', 'tanggal', 'tanggal_hijriyah',
        'kelas_id', 'bidang_ilmu_id', 'waktu_pelajaran_id',
        'jenis_kegiatan_id', 'jam_mulai', 'batas_terlambat', 'batas_alfa', 'metode', 'selesai_at',
        'dari_admin', 'aktif', // absensi kegiatan dibuat admin & dicatat bersama semua asatid
    ];

    protected $casts = [
        'tanggal' => 'date',
        'selesai_at' => 'datetime',
        'dari_admin' => 'boolean',
        'aktif' => 'boolean',
    ];

    public function detail(): HasMany
    {
        return $this->hasMany(AbsensiDetail::class);
    }

    public function pengabsen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function pelajaran(): BelongsTo
    {
        return $this->belongsTo(BidangIlmu::class, 'bidang_ilmu_id');
    }

    public function waktuPelajaran(): BelongsTo
    {
        return $this->belongsTo(WaktuPelajaran::class);
    }

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(JenisKegiatan::class, 'jenis_kegiatan_id');
    }

    /** Absensi kegiatan dari admin yang sedang dibuka untuk semua asatid (aktif & belum ditutup) */
    public function scopeKegiatanTerbuka(Builder $q): Builder
    {
        return $q->where('jenis', self::KEGIATAN)->where('dari_admin', true)->where('aktif', true)->whereNull('selesai_at');
    }

    /** Asatid ini boleh mencatat: miliknya sendiri, atau kegiatan admin yang sedang terbuka */
    public function bolehDicatatOleh(?int $userId): bool
    {
        return (int) $this->user_id === (int) $userId
            || ($this->dari_admin && $this->aktif && ! $this->selesai_at);
    }

    public function scopeTerbaru(Builder $q): Builder
    {
        return $q->orderByDesc('tanggal')->orderByDesc('id');
    }

    /** Judul singkat, mis. "Kelas Ula 1 · Nahwu" atau "Muhadharah" */
    public function getJudulAttribute(): string
    {
        if ($this->jenis === self::KEGIATAN) {
            return $this->kegiatan?->nama ?? 'Kegiatan';
        }

        return trim(($this->kelas?->nama ?? 'Kelas') . ' · ' . ($this->pelajaran?->nama ?? '-'));
    }

    /** Keterangan waktu, mis. "Jam ke: Ba'da Subuh" atau "Mulai 05.00" */
    public function getKeteranganWaktuAttribute(): string
    {
        if ($this->jenis === self::KEGIATAN) {
            return 'Mulai ' . substr((string) $this->jam_mulai, 0, 5);
        }

        return $this->waktuPelajaran?->nama ?? '-';
    }

    /** Tanggal Hijriyah dari tanggal Masehi (kalender Umm al-Qura), mis. "1 Jumadil Awal 1448 H". */
    public static function hijriyah(string $tanggal): ?string
    {
        if (! class_exists(IntlDateFormatter::class)) {
            return null;
        }

        $fmt = new IntlDateFormatter('id_ID@calendar=islamic-umalqura', IntlDateFormatter::LONG,
            IntlDateFormatter::NONE, 'Asia/Jakarta', IntlDateFormatter::TRADITIONAL, 'd MMMM y');
        $hasil = $fmt->format(new \DateTime($tanggal . ' 12:00', new \DateTimeZone('Asia/Jakarta')));

        return $hasil ? $hasil . ' H' : null;
    }

    /**
     * Status otomatis absensi kegiatan berdasarkan jam sekarang:
     * sebelum batas terlambat = hadir, sebelum batas alfa = terlambat, setelahnya = alfa.
     */
    public function statusMenurutJam(?string $jam = null): string
    {
        $jam ??= now()->format('H:i');
        $terlambat = substr((string) $this->batas_terlambat, 0, 5);
        $alfa = substr((string) $this->batas_alfa, 0, 5);

        if ($terlambat && $jam < $terlambat) {
            return 'hadir';
        }
        if ($alfa && $jam >= $alfa) {
            return 'alfa';
        }

        return $terlambat ? 'terlambat' : 'hadir';
    }

    /** Tanggal + batas alfa kegiatan ini sudah lewat (jam WIB)? */
    public function lewatBatasAlfa(): bool
    {
        if ($this->jenis !== self::KEGIATAN || ! $this->batas_alfa || ! $this->tanggal) {
            return false;
        }

        $batas = Carbon::parse($this->tanggal->format('Y-m-d') . ' ' . substr((string) $this->batas_alfa, 0, 5), 'Asia/Jakarta');

        return now('Asia/Jakarta')->gte($batas);
    }

    /** Masih terbuka padahal batas alfa sudah lewat */
    public function sudahLewatBatasAlfa(): bool
    {
        return ! $this->selesai_at && $this->lewatBatasAlfa();
    }

    /** Tutup kalau sudah lewat batas alfa. Hasilnya true kalau absensi ini baru saja ditutup. */
    public function tutupKalauLewat(): bool
    {
        if (! $this->sudahLewatBatasAlfa()) {
            return false;
        }
        $this->tutup();

        return true;
    }

    /** Tutup semua absensi kegiatan yang sudah lewat batas alfa (dipanggil saat halaman absensi dibuka) */
    public static function tutupYangLewat(): void
    {
        static::where('jenis', self::KEGIATAN)
            ->whereNull('selesai_at')
            ->whereNotNull('batas_alfa')
            ->whereDate('tanggal', '<=', now('Asia/Jakarta')->toDateString())
            ->get()
            ->each->tutupKalauLewat();
    }

    /** Tutup absensi kegiatan: peserta yang belum tercatat ditandai Alfa */
    public function tutup(): void
    {
        DB::transaction(function () {
            // Kunci baris ini supaya dua orang yang menutup bersamaan tidak menulis Alfa dua kali
            $segar = static::whereKey($this->id)->lockForUpdate()->first();
            if (! $segar || $segar->selesai_at) {
                $this->refresh();

                return;
            }

            $sudah = AbsensiDetail::where('absensi_id', $this->id)->pluck('pendaftaran_siswa_id');
            $sekarang = now();

            $baru = PendaftaranSiswa::where('status', PendaftaranSiswa::DITERIMA)
                ->whereNotIn('id', $sudah)->pluck('id')
                ->map(fn ($id) => [
                    'absensi_id' => $this->id, 'pendaftaran_siswa_id' => $id, 'status' => 'alfa',
                    'created_at' => $sekarang, 'updated_at' => $sekarang,
                ]);

            foreach ($baru->chunk(500) as $potong) {
                AbsensiDetail::insert($potong->all());
            }

            $this->update(['selesai_at' => $sekarang] + ($this->dari_admin ? ['aktif' => false] : []));
        });
    }
}
