<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Penilaian > Nilai Harian: nilai satu santri untuk satu kitab dalam satu BULAN AJARAN.
 * Asatid meneruskan nilai yang sama setiap hari (tidak membuat nilai baru tiap hari);
 * kolom "tanggal" = terakhir dinilai.
 * Enam penilaian dan batas nilainya ada di ASPEK (jumlah semua batas = 100).
 * Kolom "total" dihitung otomatis setiap kali disimpan.
 */
class NilaiHarian extends Model
{
    /** Kolom => [label, nilai maksimal]. Urutan ini dipakai di semua tampilan. */
    public const ASPEK = [
        'menjawab_pertanyaan'   => ['Menjawab pertanyaan guru', 20],
        'kelengkapan_belajar'   => ['Kelengkapan belajar', 10],
        'konsentrasi_keaktifan' => ['Konsentrasi dan keaktifan dalam kelas', 20],
        'menulis_faidah'        => ['Kecakapan dalam menulis faidah', 15],
        'menjawab_sistematis'   => ['Menjawab dengan tepat secara sistematis', 20],
        'tugas_tepat_waktu'     => ['Mengerjakan tugas tepat waktu', 15],
    ];

    protected $table = 'nilai_harian';

    protected $fillable = [
        'pendaftaran_siswa_id', 'kelas_id', 'kitab_id', 'bulan_ajaran_id', 'tanggal', 'dinilai_oleh',
        'menjawab_pertanyaan', 'kelengkapan_belajar', 'konsentrasi_keaktifan',
        'menulis_faidah', 'menjawab_sistematis', 'tugas_tepat_waktu',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    protected static function booted(): void
    {
        // Total selalu = jumlah 6 penilaian (tiap penilaian dibatasi maksimalnya)
        static::saving(function (NilaiHarian $n) {
            $total = 0;
            foreach (self::ASPEK as $kolom => [, $maks]) {
                $n->{$kolom} = max(0, min($maks, (int) $n->{$kolom}));
                $total += $n->{$kolom};
            }
            $n->total = $total;
        });
    }

    /** Daftar penilaian untuk Vue: [{kunci, label, maks}] (label sudah diterjemahkan) */
    public static function daftarAspek(): array
    {
        return collect(self::ASPEK)
            ->map(fn ($a, $kunci) => ['kunci' => $kunci, 'label' => __($a[0]), 'maks' => $a[1]])
            ->values()->all();
    }

    /** Aturan validasi: tiap penilaian wajib angka 0 sampai maksimalnya */
    public static function aturan(): array
    {
        return collect(self::ASPEK)
            ->mapWithKeys(fn ($a, $kolom) => [$kolom => ['required', 'integer', 'min:0', 'max:' . $a[1]]])
            ->all();
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(PendaftaranSiswa::class, 'pendaftaran_siswa_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function kitab(): BelongsTo
    {
        return $this->belongsTo(Kitab::class);
    }

    public function bulanAjaran(): BelongsTo
    {
        return $this->belongsTo(BulanAjaran::class);
    }

    /** Asatid yang menilai */
    public function penilai(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dinilai_oleh');
    }

    /** Satu baris untuk Vue */
    public function baris(): array
    {
        $s = $this->siswa;

        return [
            'id'       => $this->id,
            'siswa_id' => $this->pendaftaran_siswa_id,
            'siswa'    => $s?->nama_lengkap ?? __('(santri dihapus)'),
            'arab'     => $s?->nama_lengkap_arab,
            'nomor'    => $s?->nomor_pendaftaran,
            'foto'     => $s?->foto_url,
            'kelas_id' => $this->kelas_id,
            'kelas'    => $this->kelas?->nama,
            'kitab_id' => $this->kitab_id,
            'kitab'    => $this->kitab?->nama,
            'bulan_id' => $this->bulan_ajaran_id,
            'bulan'    => $this->bulanAjaran?->nama,
            'tanggal'  => $this->tanggal->toDateString(),
            'hari'     => $this->tanggal->locale('id')->translatedFormat('l'),
            'nilai'    => collect(self::ASPEK)->mapWithKeys(fn ($a, $k) => [$k => (int) $this->{$k}])->all(),
            'total'    => (int) $this->total,
            'oleh'     => $this->penilai?->name,
        ];
    }
}
