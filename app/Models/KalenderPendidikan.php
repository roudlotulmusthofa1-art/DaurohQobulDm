<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

/**
 * Kalender Pendidikan: kegiatan dari tanggal_mulai sampai tanggal_selesai (Masehi).
 * Tanggal Hijriyah dihitung otomatis (kalender Umm al-Qura, sama dengan Tahun & Bulan Ajaran).
 * Dipakai di Admin > Akademik > Kalender Pendidikan dan kotak ringkas di Dasbor admin.
 */
class KalenderPendidikan extends Model
{
    protected $table = 'kalender_pendidikan';

    protected $fillable = ['judul', 'warna', 'tanggal_mulai', 'tanggal_selesai', 'keterangan', 'dibuat_oleh'];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    // ================= Warna kegiatan =================

    /** Warna bawaan kegiatan baru */
    public const WARNA_AWAL = '#059669';

    /** Nama warna (12) x kecerahan (terang, sedang, tua) => kode hex. Selain ini admin boleh memilih warna bebas. */
    public const NAMA_WARNA = ['Merah', 'Oranye', 'Kuning', 'Hijau muda', 'Hijau', 'Hijau toska', 'Biru langit', 'Biru', 'Ungu', 'Merah muda', 'Cokelat', 'Abu-abu'];

    public const KECERAHAN = ['terang', 'sedang', 'tua'];

    public const PALET = [
        ['#f87171', '#dc2626', '#991b1b'], ['#fb923c', '#ea580c', '#9a3412'], ['#fbbf24', '#d97706', '#92400e'],
        ['#a3e635', '#65a30d', '#3f6212'], ['#34d399', '#059669', '#065f46'], ['#2dd4bf', '#0d9488', '#115e59'],
        ['#38bdf8', '#0284c7', '#075985'], ['#60a5fa', '#2563eb', '#1e40af'], ['#a78bfa', '#7c3aed', '#5b21b6'],
        ['#f472b6', '#db2777', '#9d174d'], ['#c08457', '#92633a', '#5c3d22'], ['#94a3b8', '#475569', '#1e293b'],
    ];

    /** Pilihan warna untuk formulir, urut per kecerahan: [['hex', 'label'], ...] (36 warna) */
    public static function pilihanWarna(): array
    {
        $hasil = [];
        foreach (self::KECERAHAN as $k => $kecerahan) {
            foreach (self::PALET as $i => $tiga) {
                $hasil[] = ['hex' => $tiga[$k], 'label' => __(self::NAMA_WARNA[$i]) . ' · ' . __($kecerahan)];
            }
        }

        return $hasil;
    }

    /** Kode warna yang sah ("#a1b2c3"); selain itu = warna bawaan */
    public static function rapikanWarna(?string $hex): string
    {
        return is_string($hex) && preg_match('/^#[0-9a-fA-F]{6}$/', $hex) ? strtolower($hex) : self::WARNA_AWAL;
    }

    /** Campur warna hex dengan putih/hitam: $ke = '#ffffff' atau '#000000', $porsi 0..1 */
    private static function campur(string $hex, string $ke, float $porsi): string
    {
        $a = sscanf($hex, '#%02x%02x%02x');
        $b = sscanf($ke, '#%02x%02x%02x');

        return sprintf('#%02x%02x%02x', ...array_map(fn ($x, $y) => (int) round($x + ($y - $x) * $porsi), $a, $b));
    }

    /**
     * Gaya (atribut style) dari satu warna:
     * pita = latar muda + tulisan tua + garis tepi (dipakai bersama kelas ring-1), titik = bulatan, garis = garis kemajuan.
     */
    public static function gayaWarna(?string $hex): array
    {
        $hex = self::rapikanWarna($hex);

        return [
            'pita' => 'background-color:' . self::campur($hex, '#ffffff', 0.82) . ';color:' . self::campur($hex, '#000000', 0.5)
                . ';--tw-ring-color:' . self::campur($hex, '#ffffff', 0.55),
            'titik' => 'background-color:' . $hex,
            'garis' => 'background-color:' . $hex,
        ];
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
        return [
            'id' => $this->id,
            'judul' => $this->judul,
            'warna' => self::rapikanWarna($this->warna),
            'gaya' => self::gayaWarna($this->warna),
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
        if (! Schema::hasColumn('kalender_pendidikan', 'warna')) {
            return ['berjalan' => [], 'akanDatang' => []];
        }

        $tgl = $hariIni->toDateString();

        $berjalan = static::whereDate('tanggal_mulai', '<=', $tgl)->whereDate('tanggal_selesai', '>=', $tgl)
            ->orderBy('tanggal_selesai')->take(6)->get();
        $akanDatang = static::whereDate('tanggal_mulai', '>', $tgl)
            ->whereDate('tanggal_mulai', '<=', $hariIni->copy()->addDays(30)->toDateString())
            ->orderBy('tanggal_mulai')->take(5)->get();

        return [
            'berjalan' => $berjalan->map(fn ($k) => $k->untukTampilan($hariIni))->all(),
            'akanDatang' => $akanDatang->map(fn ($k) => $k->untukTampilan($hariIni))->all(),
        ];
    }
}
