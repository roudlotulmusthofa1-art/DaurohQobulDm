<?php

namespace App\Models;

use App\Support\NamaArab;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Soal ujian buatan asatid (Asatid > Penilaian > Buat Soal Ujian) untuk satu kelompok kitab di satu ujian + halaqoh yang dipilih.
 * Kelompok kitab (kitab_ids) mengikuti cara pembuatan soal dari admin (Ujian::kelompok): satu kitab, beberapa kitab, atau seluruh fan.
 * Kolom "kitab" = salinan nama kelompok saat dikirim (mis. "jurmiyyah + متممة" / "Seluruh fan Nahwu").
 * Butir = [{pertanyaan, jawaban, nilai}]. Dikirim ke admin (status "menunggu"); admin mengedit lalu menyetujui
 * (status "disetujui" = terkirim kembali ke asatid). "butir_asli" menyimpan kiriman asatid,
 * supaya butir yang diubah admin bisa ditandai.
 */
class SoalUjian extends Model
{
    public const MENUNGGU = 'menunggu';
    public const DISETUJUI = 'disetujui';

    public const STATUS = [
        self::MENUNGGU  => 'Menunggu pemeriksaan',
        self::DISETUJUI => 'Disetujui',
    ];

    public const MAKS_BUTIR = 100;

    protected $table = 'soal_ujian';

    protected $fillable = [
        'ujian_id', 'kitab_ids', 'nama_ujian', 'fan', 'kitab', 'kelas_ids', 'butir', 'butir_asli',
        'status', 'catatan_admin', 'dibuat_oleh', 'diperiksa_oleh', 'diperiksa_at',
    ];

    protected $casts = [
        'kitab_ids'    => 'array',
        'kelas_ids'    => 'array',
        'butir'        => 'array',
        'butir_asli'   => 'array',
        'diperiksa_at' => 'datetime',
    ];

    /** Nama halaqoh (kelas) sekali ambil untuk semua baris */
    private static ?array $namaKelas = null;

    public function ujian(): BelongsTo
    {
        return $this->belongsTo(Ujian::class);
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function pemeriksa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diperiksa_oleh');
    }

    /**
     * Cek & rapikan butir dari form: tiap butir wajib punya pertanyaan, jawaban, dan nilai 1..MAKS_NILAI.
     * Hasil: [{pertanyaan, jawaban, nilai}] tanpa spasi berlebih.
     */
    public static function butirDari(Request $request): array
    {
        $butir = $request->input('butir');
        if (! is_array($butir) || ! count($butir)) {
            throw ValidationException::withMessages(['butir' => __('Tulis minimal satu pertanyaan.')]);
        }
        if (count($butir) > self::MAKS_BUTIR) {
            throw ValidationException::withMessages(['butir' => __('Paling banyak :n pertanyaan.', ['n' => self::MAKS_BUTIR])]);
        }

        $hasil = [];
        foreach (array_values($butir) as $i => $b) {
            $no = $i + 1;
            $tanya = trim((string) ($b['pertanyaan'] ?? ''));
            $jawab = trim((string) ($b['jawaban'] ?? ''));
            $nilai = $b['nilai'] ?? null;
            if ($tanya === '') {
                throw ValidationException::withMessages(["butir.$i.pertanyaan" => __('Pertanyaan nomor :n masih kosong.', ['n' => $no])]);
            }
            if ($jawab === '') {
                throw ValidationException::withMessages(["butir.$i.jawaban" => __('Jawaban nomor :n masih kosong.', ['n' => $no])]);
            }
            if (! is_numeric($nilai) || (int) $nilai < 1 || (int) $nilai > Ujian::MAKS_NILAI) {
                throw ValidationException::withMessages(["butir.$i.nilai" => __('Nilai nomor :n harus 1 sampai :maks.', ['n' => $no, 'maks' => Ujian::MAKS_NILAI])]);
            }
            if (mb_strlen($tanya) > 5000 || mb_strlen($jawab) > 10000) {
                throw ValidationException::withMessages(["butir.$i.pertanyaan" => __('Tulisan nomor :n terlalu panjang.', ['n' => $no])]);
            }
            $hasil[] = ['pertanyaan' => $tanya, 'jawaban' => $jawab, 'nilai' => (int) $nilai];
        }

        return $hasil;
    }

    /** Nomor butir (mulai 0) yang berbeda dari kiriman asatid */
    public function butirDiubah(): array
    {
        if (! $this->butir_asli) {
            return [];
        }
        $asli = array_values($this->butir_asli);
        $beda = [];
        foreach (array_values($this->butir ?? []) as $i => $b) {
            $a = $asli[$i] ?? null;
            if (! $a || $a['pertanyaan'] !== $b['pertanyaan'] || $a['jawaban'] !== $b['jawaban'] || (int) $a['nilai'] !== (int) $b['nilai']) {
                $beda[] = $i;
            }
        }

        return $beda;
    }

    /** Satu baris untuk Vue */
    public function baris(): array
    {
        self::$namaKelas ??= Kelas::pluck('nama', 'id')->all();
        $kelasIds = array_map('intval', $this->kelas_ids ?? []);
        $butir = array_values($this->butir ?? []);

        return [
            'id'            => $this->id,
            'ujian_id'      => $this->ujian_id,
            'kitab_ids'     => array_map('intval', $this->kitab_ids ?? []),
            'nama_ujian'    => $this->nama_ujian,
            'fan'           => $this->fan,
            'kitab'         => $this->kitab,
            'kelas_ids'     => $kelasIds,
            'kelas'         => array_values(array_filter(array_map(fn ($id) => self::$namaKelas[$id] ?? null, $kelasIds))),
            'butir'         => $butir,
            'butir_asli'    => $this->butir_asli ? array_values($this->butir_asli) : null,
            'diubah'        => $this->butirDiubah(),
            'jumlah_asli'   => $this->butir_asli ? count($this->butir_asli) : count($butir),
            'total'         => array_sum(array_map(fn ($b) => (int) ($b['nilai'] ?? 0), $butir)),
            'status'        => $this->status,
            'label'         => __(self::STATUS[$this->status] ?? $this->status),
            'catatan_admin' => $this->catatan_admin,
            'oleh_id'       => $this->dibuat_oleh,
            'oleh'          => $this->pembuat?->name,
            'oleh_arab'     => NamaArab::user($this->dibuat_oleh),
            'pemeriksa'     => $this->pemeriksa?->name,
            'diperiksa'     => $this->diperiksa_at?->timezone('Asia/Jakarta')->format('Y-m-d H:i'),
            'dikirim'       => $this->created_at?->timezone('Asia/Jakarta')->format('Y-m-d H:i'),
        ];
    }
}
