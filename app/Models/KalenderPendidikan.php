<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

/**
 * Kalender Pendidikan: kegiatan dari tanggal_mulai sampai tanggal_selesai (Masehi).
 * Tanggal Hijriyah dihitung otomatis (kalender Umm al-Qura, sama dengan Tahun & Bulan Ajaran).
 * Dipakai di Admin > Akademik > Kalender Pendidikan dan kotak ringkas di Dasbor admin.
 */
class KalenderPendidikan extends Model
{
    protected $table = 'kalender_pendidikan';

    protected $fillable = ['judul', 'jenis_id', 'tanggal_mulai', 'tanggal_selesai', 'keterangan', 'dibuat_oleh'];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'jenis_id' => 'integer',
    ];

    /** Jenis kegiatan buatan admin (nama + warna). Kosong = "Tanpa jenis". */
    public function jenis(): BelongsTo
    {
        return $this->belongsTo(JenisKalender::class, 'jenis_id');
    }

    // ================= Hijriyah =================

    private static function kalenderHijriyah(): \IntlCalendar
    {
        return \IntlCalendar::createInstance(new \DateTimeZone('Asia/Jakarta'), 'id_ID@calendar=islamic-umalqura');
    }

    /** [hari, bulan (1-12), tahun] Hijriyah dari tanggal Masehi */
    public static function bagianHijriyah(CarbonInterface|string $tanggal): array
    {
        $t = Carbon::parse(is_string($tanggal) ? $tanggal : $tanggal->toDateString(), 'Asia/Jakarta')->setTime(12, 0);
        $kal = self::kalenderHijriyah();
        $kal->setTime($t->getTimestampMs());

        return [$kal->get(\IntlCalendar::FIELD_DAY_OF_MONTH), $kal->get(\IntlCalendar::FIELD_MONTH) + 1, $kal->get(\IntlCalendar::FIELD_YEAR)];
    }

    /** Nama bulan Hijriyah (1-12), mis. 6 => "Jumadil Akhirah" */
    public static function namaBulanHijriyah(int $bulan): string
    {
        return __(BulanAjaran::NAMA_HIJRIYAH[($bulan - 1) % 12] ?? '');
    }

    /** Mis. "21 Jumadil Akhirah 1448 H" */
    public static function hijriyah(CarbonInterface|string $tanggal): string
    {
        [$h, $b, $y] = self::bagianHijriyah($tanggal);

        return $h . ' ' . self::namaBulanHijriyah($b) . ' ' . $y . ' H';
    }

    /** Tanggal Masehi awal & akhir satu bulan Hijriyah: [Carbon awal, Carbon akhir] */
    public static function rentangBulanHijriyah(int $tahun, int $bulan): array
    {
        $kal = self::kalenderHijriyah();
        $kal->clear();
        $kal->set(\IntlCalendar::FIELD_YEAR, $tahun);
        $kal->set(\IntlCalendar::FIELD_MONTH, $bulan - 1);
        $kal->set(\IntlCalendar::FIELD_DAY_OF_MONTH, 1);
        $kal->set(\IntlCalendar::FIELD_HOUR_OF_DAY, 12);
        $awal = Carbon::createFromTimestampMs($kal->getTime(), 'Asia/Jakarta')->startOfDay();
        $kal->add(\IntlCalendar::FIELD_MONTH, 1);
        $akhir = Carbon::createFromTimestampMs($kal->getTime(), 'Asia/Jakarta')->startOfDay()->subDay();

        return [$awal, $akhir];
    }

    // ================= Ukuran kegiatan =================

    /** Lama kegiatan dalam hari (mulai & selesai ikut dihitung) */
    public function lamaHari(): int
    {
        return (int) $this->tanggal_mulai->diffInDays($this->tanggal_selesai) + 1;
    }

    /**
     * Kemajuan kegiatan terhadap hari ini:
     * ['status' => belum|berjalan|selesai, 'hari_ke', 'lama', 'persen', 'sisa' (hari tersisa), 'lagi' (hari sampai mulai)]
     */
    public function kemajuan(?CarbonInterface $hariIni = null): array
    {
        $hariIni = Carbon::parse(($hariIni ?? now('Asia/Jakarta'))->toDateString());
        $mulai = Carbon::parse($this->tanggal_mulai->toDateString());
        $selesai = Carbon::parse($this->tanggal_selesai->toDateString());
        $lama = $this->lamaHari();

        if ($hariIni->lt($mulai)) {
            return ['status' => 'belum', 'hari_ke' => 0, 'lama' => $lama, 'persen' => 0, 'sisa' => $lama, 'lagi' => (int) $hariIni->diffInDays($mulai)];
        }
        if ($hariIni->gt($selesai)) {
            return ['status' => 'selesai', 'hari_ke' => $lama, 'lama' => $lama, 'persen' => 100, 'sisa' => 0, 'lagi' => 0];
        }

        $ke = (int) $mulai->diffInDays($hariIni) + 1;

        return ['status' => 'berjalan', 'hari_ke' => $ke, 'lama' => $lama, 'persen' => (int) round($ke / $lama * 100), 'sisa' => $lama - $ke, 'lagi' => 0];
    }

    /** Data satu kegiatan untuk Vue (tanggal Masehi + Hijriyah + kemajuan) */
    public function untukTampilan(?CarbonInterface $hariIni = null): array
    {
        $jenis = $this->jenis_id ? $this->jenis : null;

        return [
            'id' => $this->id,
            'judul' => $this->judul,
            'jenis_id' => $jenis?->id,
            'label_jenis' => $jenis?->nama ?? __('Tanpa jenis'),
            'kelas' => JenisKalender::kelas($jenis?->warna),
            'mulai' => $this->tanggal_mulai->toDateString(),
            'selesai' => $this->tanggal_selesai->toDateString(),
            'mulai_teks' => $this->tanggal_mulai->locale(app()->getLocale())->translatedFormat('d M Y'),
            'selesai_teks' => $this->tanggal_selesai->locale(app()->getLocale())->translatedFormat('d M Y'),
            'mulai_hijriyah' => self::hijriyah($this->tanggal_mulai),
            'selesai_hijriyah' => self::hijriyah($this->tanggal_selesai),
            'keterangan' => $this->keterangan,
            'kemajuan' => $this->kemajuan($hariIni),
        ];
    }

    /**
     * Ringkasan untuk Dasbor admin: kegiatan yang sedang berjalan + 5 kegiatan terdekat (30 hari ke depan).
     * Tabel belum ada (migrasi belum dijalankan) = kosong, dasbor tetap terbuka.
     */
    public static function ringkasDasbor(CarbonInterface $hariIni): array
    {
        if (! Schema::hasColumn('kalender_pendidikan', 'jenis_id')) {
            return ['berjalan' => [], 'akanDatang' => []];
        }

        $tgl = $hariIni->toDateString();

        $berjalan = static::with('jenis')->whereDate('tanggal_mulai', '<=', $tgl)->whereDate('tanggal_selesai', '>=', $tgl)
            ->orderBy('tanggal_selesai')->take(6)->get();
        $akanDatang = static::with('jenis')->whereDate('tanggal_mulai', '>', $tgl)
            ->whereDate('tanggal_mulai', '<=', $hariIni->copy()->addDays(30)->toDateString())
            ->orderBy('tanggal_mulai')->take(5)->get();

        return [
            'berjalan' => $berjalan->map(fn ($k) => $k->untukTampilan($hariIni))->all(),
            'akanDatang' => $akanDatang->map(fn ($k) => $k->untukTampilan($hariIni))->all(),
        ];
    }
}
