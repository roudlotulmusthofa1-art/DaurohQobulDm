<?php

namespace App\Models;

use App\Support\NamaArab;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Nilai Ujian Lisan: satu santri + satu ujian (UjianLisan) + satu tanggal = satu nilai.
 * Diisi asatid (Penilaian > Ujian Lisan) atau admin (tombol Beri Nilai). Dinilai lagi di tanggal yang sama = diperbarui.
 * Nama fan, kitab & batas nilai disalin saat menilai, jadi tetap terbaca walau pengaturannya diubah / dihapus.
 * "catatan" = catatan penilai untuk santri ini (boleh kosong), tampil di Admin > Ujian Lisan di bawah nilainya.
 */
class NilaiUjianLisan extends Model
{
    protected $table = 'nilai_ujian_lisan';

    protected $fillable = [
        'pendaftaran_siswa_id', 'ujian_lisan_id', 'fan', 'kitab', 'tanggal',
        'maks', 'nilai', 'total', 'total_maks', 'catatan', 'dinilai_oleh',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'maks'    => 'array',
        'nilai'   => 'array',
    ];

    protected static function booted(): void
    {
        // Tiap nilai dibatasi 0..batasnya; total & total maksimal selalu dihitung ulang
        static::saving(function (NilaiUjianLisan $n) {
            $maks = array_values(array_map('intval', $n->maks ?? []));
            $nilai = array_values($n->nilai ?? []);
            $n->maks = $maks;
            $n->nilai = array_map(fn ($b, $i) => max(0, min($b, (int) ($nilai[$i] ?? 0))), $maks, array_keys($maks));
            $n->total = array_sum($n->nilai);
            $n->total_maks = array_sum($maks);
        });
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(PendaftaranSiswa::class, 'pendaftaran_siswa_id');
    }

    public function ujian(): BelongsTo
    {
        return $this->belongsTo(UjianLisan::class, 'ujian_lisan_id');
    }

    public function penilai(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dinilai_oleh');
    }

    /**
     * Simpan nilai dari form (dipakai asatid & admin).
     * Isian: siswa_id, ujian_lisan_id (hanya ujian aktif), tanggal, nilai[] (sebanyak pertanyaan, tiap nilai 0..batasnya),
     * catatan (boleh kosong).
     */
    public static function simpanDari(Request $request): self
    {
        $data = $request->validate([
            'siswa_id'       => ['required', 'integer', Rule::exists('pendaftaran_siswas', 'id')->where('status', PendaftaranSiswa::DITERIMA)],
            'ujian_lisan_id' => ['required', 'integer', Rule::exists('ujian_lisan', 'id')->where('is_active', true)],
            'tanggal'        => ['required', 'date'],
            'nilai'          => ['required', 'array'],
            'catatan'        => ['nullable', 'string', 'max:5000'],
        ], [
            'siswa_id.required'       => __('Pilih santri dulu.'),
            'siswa_id.exists'         => __('Santri tidak ditemukan / belum diterima.'),
            'ujian_lisan_id.required' => __('Pilih fan yang diujikan.'),
            'ujian_lisan_id.exists'   => __('Ujian ini tidak ditemukan atau sudah dimatikan admin.'),
            'tanggal.required'        => __('Tanggal wajib diisi.'),
        ]);

        $ujian = UjianLisan::with(['bidangIlmu:id,nama', 'kitab:id,nama'])->findOrFail($data['ujian_lisan_id']);
        $maks = array_map('intval', $ujian->nilai_pertanyaan ?? []);

        // Tiap pertanyaan wajib diisi angka 0 sampai batasnya
        foreach ($maks as $i => $batas) {
            $v = $data['nilai'][$i] ?? null;
            if ($v === null || $v === '' || ! is_numeric($v) || (int) $v < 0 || (int) $v > $batas) {
                throw ValidationException::withMessages([
                    "nilai.$i" => __('Nilai pertanyaan :n harus 0 sampai :maks.', ['n' => $i + 1, 'maks' => $batas]),
                ]);
            }
        }

        return self::updateOrCreate(
            ['pendaftaran_siswa_id' => $data['siswa_id'], 'ujian_lisan_id' => $ujian->id, 'tanggal' => $data['tanggal']],
            [
                'fan'          => $ujian->bidangIlmu?->nama,
                'kitab'        => $ujian->kitab?->nama,
                'maks'         => $maks,
                'nilai'        => array_map(fn ($i) => (int) $data['nilai'][$i], array_keys($maks)),
                'catatan'      => trim((string) ($data['catatan'] ?? '')) ?: null,
                'dinilai_oleh' => Auth::id(),
            ],
        )->load(['siswa.kelasMaster', 'penilai:id,name']);
    }

    /** Satu baris untuk Vue */
    public function baris(): array
    {
        $s = $this->siswa;

        return [
            'id'         => $this->id,
            'siswa_id'   => $this->pendaftaran_siswa_id,
            'siswa'      => $s?->nama_lengkap ?? __('(santri dihapus)'),
            'arab'       => $s?->nama_lengkap_arab,
            'nomor'      => $s?->nomor_pendaftaran,
            'kelas'      => $s?->kelasMaster?->nama,
            'foto'       => $s?->foto_url,
            'ujian_id'   => $this->ujian_lisan_id,
            'fan'        => $this->fan,
            'kitab'      => $this->kitab,
            'tanggal'    => $this->tanggal->toDateString(),
            'maks'       => array_map('intval', $this->maks ?? []),
            'nilai'      => array_map('intval', $this->nilai ?? []),
            'total'      => (int) $this->total,
            'total_maks' => (int) $this->total_maks,
            'persen'     => $this->total_maks ? round($this->total / $this->total_maks * 100) : 0,
            'catatan'    => $this->catatan,
            'oleh'       => $this->penilai?->name,
            'oleh_arab'  => NamaArab::user($this->dinilai_oleh),
            'oleh_id'    => $this->dinilai_oleh,
        ];
    }
}
