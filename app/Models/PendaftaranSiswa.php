<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class PendaftaranSiswa extends Model
{
    protected $table = 'pendaftaran_siswas';

    protected $guarded = ['id'];

    protected $hidden = ['password'];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'diproses_at'   => 'datetime',
        'setuju'        => 'boolean',
        'riwayat_pondok' => 'array',
        'riwayat_kitab' => 'array',
        'syarat_uang_pendaftaran' => 'boolean',
        'syarat_rekomendasi'      => 'boolean',
        'syarat_izin_orang_tua'   => 'boolean',
        'syarat_raport'           => 'boolean',
        'syarat_paspor'           => 'boolean',
        'syarat_no_paspor'        => 'string',
    ];

    public const MENUNGGU = 'menunggu';
    public const DITERIMA = 'diterima';
    public const DITOLAK  = 'ditolak';

    /** Kelulusan akhir dauroh (diisi di Akademik > Kelulusan). Bukan hasil seleksi pendaftaran. */
    public const LULUS = 'lulus';
    public const TIDAK_LULUS = 'tidak_lulus';
    public const PREDIKAT = ['Mumtaz', 'Jayyid Jiddan', 'Jayyid', 'Maqbul'];

    /** Waktu admin mengumumkan kelulusan (sesi aktif). null = belum diumumkan, peserta belum bisa melihat hasil. */
    public static function kelulusanDiumumkan(): ?\Illuminate\Support\Carbon
    {
        $waktu = Pengaturan::ambil('kelulusan_diumumkan_at');

        return $waktu ? \Illuminate\Support\Carbon::parse($waktu) : null;
    }

    /** Nomor pendaftaran: PSB20260001 (urut per tahun). Panggil di dalam DB::transaction(). */
    public static function buatNomor(): string
    {
        $prefix = 'PSB' . now()->format('Y');

        $terakhir = static::where('nomor_pendaftaran', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('nomor_pendaftaran')
            ->value('nomor_pendaftaran');

        $urut = $terakhir ? ((int) substr($terakhir, -4)) + 1 : 1;

        return $prefix . str_pad($urut, 4, '0', STR_PAD_LEFT);
    }

    /** Email login siswa (otomatis), mis. psb20260001@siswa.ribath.local */
    public function emailLogin(): string
    {
        return strtolower($this->nomor_pendaftaran) . '@siswa.ribath.local';
    }


    /**
     * Buat / perbarui akun login peserta (role siswa) dari email & password formulir.
     * Dipanggil saat mendaftar (agar bisa langsung masuk dasbor siswa) dan saat diterima.
     */
    public function buatAkun(): User
    {
        $user = $this->user_id ? User::find($this->user_id) : null;
        $user ??= User::firstOrNew(['email' => $this->email]);

        $user->forceFill([
            'name'     => $this->nama_lengkap,
            'email'    => $this->email,
            'password' => $this->password, // sudah ter-hash saat mendaftar, JANGAN di-Hash::make lagi
            'role'     => 'siswa',         // ditulis eksplisit, tidak bergantung nilai bawaan
        ])->save();

        if ($this->user_id !== $user->id) {
            $this->forceFill(['user_id' => $user->id])->saveQuietly();
        }

        return $user;
    }

    /** Akun siswa hanya boleh dipakai (login / dasbor siswa) setelah admin menyatakan DITERIMA */
    public function bolehMasukDasbor(): bool
    {
        return $this->status === self::DITERIMA;
    }

    /** Pesan untuk akun siswa yang belum boleh masuk (dipakai halaman login) */
    public function alasanBelumBolehMasuk(): string
    {
        return $this->status === self::DITOLAK
            ? 'Mohon maaf, pendaftaran Anda tidak diterima, jadi akun siswa tidak bisa dipakai.'
            : 'Pendaftaran Anda masih menunggu verifikasi admin. Akun siswa bisa dipakai setelah Anda dinyatakan DITERIMA (info dikirim lewat WhatsApp).';
    }

    /**
     * Reset password login (peserta lupa password). Password asli tidak bisa dilihat karena tersimpan
     * terenkripsi, jadi dibuatkan password baru, disimpan, lalu dikembalikan SEKALI untuk dikirim ke peserta.
     */
    public function resetPassword(): string
    {
        $huruf = 'abcdefghjkmnpqrstuvwxyz23456789'; // tanpa huruf/angka yang mirip (l, 1, o, 0, i)
        $baru = '';
        for ($i = 0; $i < 8; $i++) {
            $baru .= $huruf[random_int(0, strlen($huruf) - 1)];
        }

        $hash = \Illuminate\Support\Facades\Hash::make($baru);
        DB::transaction(function () use ($hash) {
            $this->forceFill(['password' => $hash])->save();
            if ($this->user_id || $this->status === self::DITERIMA) {
                $this->buatAkun(); // salin password baru ke akun login (users)
            }
        });

        return $baru;
    }

    public function terima(?string $catatan = null): void
    {
        DB::transaction(function () use ($catatan) {
            $user = $this->buatAkun();

            $this->update([
                'status'        => self::DITERIMA,
                'catatan_admin' => $catatan,
                'diproses_at'   => now(),
                'user_id'       => $user->id,
            ]);
        });
    }

    public function tolak(string $catatan): void
    {
        $this->update([
            'status'        => self::DITOLAK,
            'catatan_admin' => $catatan,
            'diproses_at'   => now(),
        ]);
    }

    /** Arti kode singkat di database -> tulisan yang dibaca admin */
    public const STATUS_PERNIKAHAN = [
        'K' => 'Menikah (متزوج)',
        'L' => 'Belum menikah (عازب)',
    ];

    /**
     * Chat konfirmasi untuk pendaftar yang sudah diterima / ditolak (isi diatur di Pengaturan > Pesan).
     * @return array{pesan: string, tujuan: array<string, string>}|null  null = belum diproses / pesan dimatikan
     */
    public function chatKonfirmasi(): ?array
    {
        if ($this->status === self::DITERIMA) {
            $this->loadMissing(['kelasMaster', 'kamarMaster']);
            $pesan = \App\Support\PesanWa::buat('psb_terima', [
                'nama' => $this->nama_lengkap,
                'penempatan' => $this->ringkasanPenempatan(),
                'syarat' => $this->ringkasanSyarat(),
                'catatan' => $this->catatan_admin,
                'email' => $this->email,
            ]);
            $tujuan = ['Peserta' => $this->no_hp]; // chat konfirmasi dikirim ke nomor peserta
        } elseif ($this->status === self::DITOLAK) {
            $pesan = \App\Support\PesanWa::buat('psb_tolak', ['nama' => $this->nama_lengkap, 'alasan' => $this->catatan_admin]);
            $tujuan = ['Peserta' => $this->no_hp];
        } else {
            return null;
        }

        return $pesan ? ['pesan' => $pesan, 'tujuan' => array_filter($tujuan)] : null;
    }

    /**
     * Pendaftaran (formulir /psb/daftar) sedang DIBUKA? Diatur admin di PSB > Data Masuk Peserta.
     * Disimpan di Pengaturan 'psb_dibuka' ('1' / '0'); bawaan: dibuka.
     */
    public static function pendaftaranDibuka(): bool
    {
        return rescue(fn () => Pengaturan::ambil('psb_dibuka', '1'), '1', false) !== '0';
    }

    /** "Apakah Anda pernah mengikuti Dauroh Qobul sebelumnya?" (kolom pernah_dauroh) */
    public const PERNAH_DAUROH = [
        'tidak' => 'لا (Tidak)',
        'ya' => 'نعم (Ya)',
    ];

    public const PENDIDIKAN_TERAKHIR = [
        'K' => 'Ibtidaiyah (إبتدائية)',
        'L' => 'Tsanawiyah (ثانوية)',
        'M' => 'Aliyah (عالية)',
        'o' => 'Universitas (جامعة)',
    ];

    public const FAN_KITAB = [
        'فقه',
        'أصول الفقه',
        'نحو',
        'صرف',
        'توحيد',
        'مصطلح الحديث',
        'تجويد',
        'سيرة النبي',
        'منطق',
        'وغيرها',
    ];

    /** Kolom berkas => nama yang tampil */
    public const BERKAS = [
        'berkas_foto'          => 'Pas Foto',
        'berkas_rekomendasi'   => 'Surat Rekomendasi',
        'berkas_raport_ijazah' => 'Raport Akhir / Ijazah Terakhir',
        'berkas_izin_belajar'  => 'Surat Izin Belajar',
    ];

    /** Kolom persyaratan => nama yang tampil (menambah syarat baru: tambah kolom di migrasi + satu baris di sini) */
    public const SYARAT = [
        'syarat_uang_pendaftaran' => 'Uang Pendaftaran',
        'syarat_rekomendasi'      => 'Surat Rekomendasi',
        'syarat_izin_orang_tua'   => 'Surat Izin Orang Tua',
        'syarat_raport'           => 'Raport',
        'syarat_paspor'           => 'Paspor',


    ];

    /**
     * LAMA — tidak dipakai lagi. Pilihan kelas & kamar sekarang diambil dari
     * Master Data (relasi kelasMaster() dan kamarMaster()). Dibiarkan agar
     * mudah dikembalikan bila perlu.
     */
    public const KELAS = [
        1 => 'Kelas 1',
        2 => 'Kelas 2',
        3 => 'Kelas 3',
        4 => 'Kelas 4',
        5 => 'Kelas 5',
        6 => 'Kelas 6',
        7 => 'Kelas 7',
        8 => 'Kelas 8',
        9 => 'Kelas 9',
        10 => 'Kelas 10',
    ];

    /** Pilihan kamar: nomor (disimpan di database) => nama yang tampil. GANTI dengan nama kamar yang sebenarnya. */
    public const KAMAR = [
        1 => 'Kamar 1',
        2 => 'Kamar 2',
        3 => 'Kamar 3',
        4 => 'Kamar 4',
        5 => 'Kamar 5',
        6 => 'Kamar 6',
        7 => 'Kamar 7',
        8 => 'Kamar 8',
        9 => 'Kamar 9',
        10 => 'Kamar 10',
    ];
    /** Syarat yang punya berkas unggahan (untuk memudahkan admin memeriksa) */
    public const SYARAT_BERKAS = [
        'syarat_rekomendasi'    => 'berkas_rekomendasi',
        'syarat_izin_orang_tua' => 'berkas_izin_belajar',
        'syarat_raport'         => 'berkas_raport_ijazah',
    ];

    /** Dijalankan otomatis oleh Laravel: setiap pendaftar BARU langsung diberi kode QR. */
    protected static function booted(): void
    {
        static::creating(function (self $p) {
            if (empty($p->kode_qr)) {
                $p->kode_qr = static::buatKodeQr();
            }
        });

        // Pindah kamar -> nomor kasur lama dikosongkan (kecuali nomor kasur ikut diisi, mis. dari denah kamar)
        static::saving(function (self $p) {
            if ($p->isDirty('kamar_id') && ! $p->isDirty('nomor_kasur')) {
                $p->nomor_kasur = null;
            }
        });
    }

    /** Kode acak 16 karakter; diulang kalau kebetulan sudah dipakai. */
    public static function buatKodeQr(): string
    {
        do {
            $kode = Str::random(16);
        } while (static::where('kode_qr', $kode)->exists());

        return $kode;
    }

    /** Gambar QR (SVG). Dipakai di view sebagai {!! $p->qr_svg !!} */
    public function getQrSvgAttribute(): ?string
    {
        if (! $this->kode_qr) {
            return null;
        }

        $renderer = new ImageRenderer(new RendererStyle(220), new SvgImageBackEnd());

        return (new Writer($renderer))->writeString($this->kode_qr);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Kelas penempatan dari Master Data (kolom kelas_id). Pakai: $p->kelasMaster?->nama */
    /** Pesan antara admin dan peserta ini (dasbor siswa) */
    public function pesan()
    {
        return $this->hasMany(PesanPeserta::class);
    }

    public function kelasMaster()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    /** Kamar penempatan dari Master Data (kolom kamar_id). Pakai: $p->kamarMaster?->nama */
    public function kamarMaster()
    {
        return $this->belongsTo(Kamar::class, 'kamar_id');
    }

    /** Alamat dalam satu baris: alamat، kecamatan، kota، provinsi */
    public function getAlamatSingkatAttribute(): ?string
    {
        $bagian = collect([$this->alamat, $this->kecamatan, $this->kota, $this->provinsi])
            ->map(fn ($v) => trim((string) $v))->filter()->unique();

        return $bagian->isNotEmpty() ? $bagian->implode('، ') : null;
    }

    /** Riwayat mondok terakhir (baris terakhir riwayat_pondok), mis. "معهد دار السلام، باندونغ" */
    public function getPondokTerakhirAttribute(): ?string
    {
        $akhir = collect($this->riwayat_pondok ?? [])->filter(fn ($r) => filled($r['asal_pondok'] ?? null))->last();
        if (! $akhir) {
            return null;
        }

        return collect([$akhir['asal_pondok'] ?? null, $akhir['nama_daerah'] ?? null])
            ->map(fn ($v) => trim((string) $v))->filter()->implode('، ');
    }

    public function getFotoUrlAttribute(): ?string
    {
        return $this->berkas_foto ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->berkas_foto) : null;
    }

    public function getLabelStatusAttribute(): string
    {
        return match ($this->status) {
            self::DITERIMA => 'Diterima',
            self::DITOLAK  => 'Ditolak',
            default        => 'Menunggu Verifikasi',
        };
    }

    /** Teks untuk chat WhatsApp: ✅ Uang Pendaftaran / ❌ Paspor / ... */
    public function ringkasanSyarat(): string
    {
        return collect(self::SYARAT)
            ->map(fn($nama, $kolom) => ($this->$kolom ? '✅ ' : '❌ ') . $nama)
            ->implode("\n");
    }

    /** Teks penempatan untuk chat WhatsApp; kosong kalau kelas dan kamar belum dipilih */
    public function ringkasanPenempatan(): string
    {
        $baris = [];

        // Nama diambil dari Master Data (kelas_id / kamar_id)
        if ($this->kelasMaster) {
            $baris[] = 'Kelas: *' . $this->kelasMaster->nama . '*';
        }

        if ($this->kamarMaster) {
            $baris[] = 'Kamar: *' . $this->kamarMaster->nama . '*';
        }

        return implode("\n", $baris);
    }
    /** Jumlah syarat yang sudah dicentang. Dipakai di view sebagai $p->syarat_terpenuhi */
    public function getSyaratTerpenuhiAttribute(): int
    {
        return collect(self::SYARAT)->filter(fn($nama, $kolom) => $this->$kolom)->count();
    }
    /**
     * Ubah nomor HP jadi link WhatsApp: 0812-3456 / +62 812 3456 -> https://wa.me/628123456
     * $pesan (opsional) = isi chat yang sudah terketik; kosongkan agar admin mengetik sendiri.
     */
    public static function linkWa(?string $nomor, ?string $pesan = null): ?string
    {
        $n = preg_replace('/\D/', '', (string) $nomor); // buang semua selain angka

        if ($n === '') {
            return null;
        }

        if (str_starts_with($n, '0')) {
            $n = '62' . substr($n, 1); // 0812... -> 62812...
        }

        return 'https://wa.me/' . $n . ($pesan ? '?text=' . rawurlencode($pesan) : '');
    }

    public function scopeCari($q, ?string $kata)
    {
        return $kata
            ? $q->where(fn($w) => $w->where('nama_lengkap', 'like', "%$kata%")
                ->orWhere('nomor_pendaftaran', 'like', "%$kata%")
                ->orWhere('nama_wali', 'like', "%$kata%"))
            : $q;
    }


}
