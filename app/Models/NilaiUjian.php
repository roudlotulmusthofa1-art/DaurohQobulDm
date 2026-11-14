<?php

namespace App\Models;

use App\Support\DataCatatan;
use App\Support\NamaArab;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Nilai Ujian (tulis): satu santri + satu ujian + satu KELOMPOK kitab = satu nilai (satu angka 0..nilai maksimal ujian).
 * Kelompok mengikuti cara pembuatan soal dari admin (Ujian::kelompok): per kitab / seluruh fan / kelompok kitab.
 * kitab_ids = kitab yang dicakup, kelompok = kunci "2-3", kitab = salinan nama kelompok (mis. "jurmiyyah + متممة").
 * Diisi asatid (Asatid > Penilaian > Ujian > Nilai Ujian); admin bisa mengubah / menghapus di tab Ujian.
 * Nama ujian, fan & kitab disalin saat menilai, jadi tetap terbaca walau pengaturannya diubah / dihapus.
 */
class NilaiUjian extends Model
{
    protected $table = 'nilai_ujian';

    protected $fillable = [
        'pendaftaran_siswa_id', 'ujian_id', 'kitab_ids', 'kelompok', 'kelas_id', 'nama_ujian', 'fan', 'kitab',
        'nilai', 'nilai_maks', 'tanggal', 'dinilai_oleh',
    ];

    protected $casts = [
        'tanggal'   => 'date',
        'kitab_ids' => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (NilaiUjian $n) {
            $n->nilai = max(0, min((int) $n->nilai_maks, (int) $n->nilai));
        });
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(PendaftaranSiswa::class, 'pendaftaran_siswa_id');
    }

    public function ujian(): BelongsTo
    {
        return $this->belongsTo(Ujian::class);
    }

    public function halaqoh(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function penilai(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dinilai_oleh');
    }

    /** Relasi yang dipakai baris() */
    public const MUAT = ['siswa.kelasMaster', 'halaqoh:id,nama', 'penilai:id,name'];

    /**
     * Simpan nilai dari form asatid. Isian: ujian_id (aktif), kitab_ids (harus sama dengan salah satu kelompok ujian itu),
     * kelas_id (halaqoh), tanggal, nilai[siswa_id] = angka (kosong = dilewati).
     * Mengembalikan nilai yang tersimpan.
     */
    public static function simpanDari(Request $request): array
    {
        $data = $request->validate([
            'ujian_id' => ['required', 'integer', Rule::exists('ujian', 'id')->where('is_active', true)],
            'kitab_ids'   => ['required', 'array', 'min:1'],
            'kitab_ids.*' => ['integer'],
            'kelas_id' => ['required', 'integer', 'exists:kelas,id'],
            'tanggal'  => ['required', 'date'],
            'nilai'    => ['required', 'array'],
        ], [
            'ujian_id.required' => __('Pilih ujian dulu.'),
            'ujian_id.exists'   => __('Ujian ini tidak ditemukan atau sudah dimatikan admin.'),
            'kitab_ids.required' => __('Pilih kitab dulu.'),
            'kitab_ids.min'      => __('Pilih kitab dulu.'),
            'kelas_id.required' => __('Pilih halaqoh dulu.'),
            'nilai.required'    => __('Isi nilai minimal satu santri.'),
        ]);

        // Kitab yang dinilai harus sama persis dengan salah satu kelompok dari pengaturan admin (sama seperti soal)
        $ujian = Ujian::with(['bidangIlmu:id,nama', 'kitab:kitab.id,kitab.nama'])->findOrFail($data['ujian_id']);
        $kelompok = $ujian->cariKelompok($data['kitab_ids']);
        if (! $kelompok) {
            throw ValidationException::withMessages(['kitab_ids' => __('Pilihan kitab tidak sesuai pengaturan ujian dari admin.')]);
        }
        $maks = (int) $ujian->nilai_maks;

        // Hanya santri halaqoh itu yang boleh dinilai di sini
        $santri = PendaftaranSiswa::where('status', PendaftaranSiswa::DITERIMA)->where('kelas_id', $data['kelas_id'])
            ->whereIn('id', array_keys($data['nilai']))->pluck('nama_lengkap', 'id');

        $isi = [];
        foreach ($data['nilai'] as $siswaId => $v) {
            if ($v === null || $v === '') {
                continue;
            }
            if (! isset($santri[$siswaId])) {
                continue;
            }
            if (! is_numeric($v) || (int) $v < 0 || (int) $v > $maks) {
                throw ValidationException::withMessages([
                    "nilai.$siswaId" => __('Nilai :nama harus 0 sampai :maks.', ['nama' => $santri[$siswaId], 'maks' => $maks]),
                ]);
            }
            $isi[$siswaId] = (int) $v;
        }
        if (! $isi) {
            throw ValidationException::withMessages(['nilai' => __('Isi nilai minimal satu santri.')]);
        }

        $tersimpan = [];
        foreach ($isi as $siswaId => $v) {
            $tersimpan[] = self::updateOrCreate(
                ['pendaftaran_siswa_id' => $siswaId, 'ujian_id' => $ujian->id, 'kelompok' => $kelompok['kunci']],
                [
                    'kitab_ids'    => $kelompok['kitab_ids'],
                    'kelas_id'     => $data['kelas_id'],
                    'nama_ujian'   => $ujian->nama,
                    'fan'          => $ujian->bidangIlmu?->nama,
                    'kitab'        => $kelompok['nama'],
                    'nilai'        => $v,
                    'nilai_maks'   => $maks,
                    'tanggal'      => $data['tanggal'],
                    'dinilai_oleh' => Auth::id(),
                ],
            );
        }

        return $tersimpan;
    }

    /** Satu baris untuk Vue */
    public function baris(): array
    {
        return DataCatatan::siswa($this->siswa) + [
            'id'         => $this->id,
            'ujian_id'   => $this->ujian_id,
            'kitab_ids'  => array_map('intval', $this->kitab_ids ?? []),
            'kelompok'   => (string) $this->kelompok,
            'kelas_id'   => $this->kelas_id,
            'halaqoh'    => $this->halaqoh?->nama,
            'nama_ujian' => $this->nama_ujian,
            'fan'        => $this->fan,
            'kitab'      => $this->kitab,
            'nilai'      => (int) $this->nilai,
            'nilai_maks' => (int) $this->nilai_maks,
            'persen'     => $this->nilai_maks ? round($this->nilai / $this->nilai_maks * 100) : 0,
            'tanggal'    => $this->tanggal->toDateString(),
            'oleh'       => $this->penilai?->name,
            'oleh_arab'  => NamaArab::user($this->dinilai_oleh),
            'oleh_id'    => $this->dinilai_oleh,
        ];
    }
}
