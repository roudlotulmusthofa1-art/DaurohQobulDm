<?php

namespace App\Models;

use App\Support\DataCatatan;
use App\Support\NamaArab;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Penilaian > Perizinan: satu izin untuk satu santri (dari tanggal+jam mulai sampai tanggal+jam berakhir).
 * Satu santri boleh punya beberapa izin dalam sehari.
 * Semua waktu disimpan dalam WIB sebagai tulisan "Y-m-d H:i:s"; dibandingkan dengan sekarang() (bukan now() biasa, server UTC).
 *
 * Status:
 *   belum     : belum sampai jam mulai
 *   sedang    : sedang izin (belum kembali, batas belum lewat)
 *   terlambat : kembali setelah batas, atau batas sudah lewat tapi belum kembali -> masuk catatan pelanggaran
 *   tepat     : sudah kembali sebelum / tepat batas
 */
class IzinSantri extends Model
{
    public const STATUS = [
        'sedang'    => 'Sedang izin',
        'belum'     => 'Belum mulai',
        'terlambat' => 'Terlambat',
        'tepat'     => 'Kembali tepat waktu',
    ];

    protected $table = 'izin_santri';

    protected $fillable = ['pendaftaran_siswa_id', 'jenis', 'mulai', 'selesai', 'kembali', 'catatan', 'diberi_oleh'];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(PendaftaranSiswa::class, 'pendaftaran_siswa_id');
    }

    public function pemberi(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diberi_oleh');
    }

    /** Waktu sekarang dalam WIB, bentuk "Y-m-d H:i:s" (sama dengan isi kolom) */
    public static function sekarang(): string
    {
        return now('Asia/Jakarta')->format('Y-m-d H:i:s');
    }

    /** Izin yang terlambat: kembali > batas, atau belum kembali padahal batas sudah lewat */
    public function scopeTerlambat(Builder $q): Builder
    {
        return $q->where(fn ($w) => $w->whereColumn('kembali', '>', 'selesai')
            ->orWhere(fn ($b) => $b->whereNull('kembali')->where('selesai', '<', self::sekarang())));
    }

    /** Isi kolom apa adanya ("Y-m-d H:i:s", WIB) */
    private function mentah(string $kolom): ?string
    {
        $nilai = $this->getAttributes()[$kolom] ?? null;

        return $nilai ? substr((string) $nilai, 0, 19) : null;
    }

    /** Kolom waktu sebagai Carbon WIB */
    public function waktu(string $kolom): ?Carbon
    {
        $nilai = $this->mentah($kolom);

        return $nilai ? Carbon::parse($nilai, 'Asia/Jakarta') : null;
    }

    public function status(): string
    {
        $sekarang = self::sekarang();
        $selesai = $this->mentah('selesai');
        $kembali = $this->mentah('kembali');

        if ($kembali) {
            return $kembali > $selesai ? 'terlambat' : 'tepat';
        }
        if ($selesai < $sekarang) {
            return 'terlambat';
        }

        return $this->mentah('mulai') > $sekarang ? 'belum' : 'sedang';
    }

    /** Berapa menit terlambat (sampai kembali, atau sampai sekarang kalau belum kembali) */
    public function menitTerlambat(): int
    {
        $batas = $this->waktu('selesai');
        $akhir = $this->waktu('kembali') ?? now('Asia/Jakarta');

        return $akhir->greaterThan($batas) ? (int) $batas->diffInMinutes($akhir) : 0;
    }

    /** Lama dalam tulisan, mis. "1 jam 20 menit" / "2 hari 3 jam" */
    public static function lama(int $menit): string
    {
        $hari = intdiv($menit, 1440);
        $jam = intdiv($menit % 1440, 60);
        $sisa = $menit % 60;

        $bagian = [];
        if ($hari) {
            $bagian[] = $hari . ' ' . __('hari');
        }
        if ($jam) {
            $bagian[] = $jam . ' ' . __('jam');
        }
        if ($sisa || ! $bagian) {
            $bagian[] = $sisa . ' ' . __('menit');
        }

        return implode(' ', $bagian);
    }

    /** Satu baris untuk Vue */
    public function baris(): array
    {
        $mulai = $this->waktu('mulai');
        $selesai = $this->waktu('selesai');
        $kembali = $this->waktu('kembali');
        $status = $this->status();
        $telat = $status === 'terlambat' ? $this->menitTerlambat() : 0;

        return DataCatatan::siswa($this->siswa) + [
            'id'              => $this->id,
            'jenis'           => $this->jenis,
            'mulai_tanggal'   => $mulai->toDateString(),
            'mulai_jam'       => $mulai->format('H:i'),
            'selesai_tanggal' => $selesai->toDateString(),
            'selesai_jam'     => $selesai->format('H:i'),
            'kembali_tanggal' => $kembali?->toDateString(),
            'kembali_jam'     => $kembali?->format('H:i'),
            'hari'            => $mulai->locale('id')->translatedFormat('l'),
            'status'          => $status,
            'label'           => __(self::STATUS[$status]),
            'telat'           => $telat ? self::lama($telat) : null,
            'catatan'         => $this->catatan,
            'oleh'            => $this->pemberi?->name,
            'oleh_arab'       => NamaArab::user($this->diberi_oleh),
        ];
    }
}
