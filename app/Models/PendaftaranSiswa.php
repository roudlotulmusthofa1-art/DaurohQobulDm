<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class PendaftaranSiswa extends Model
{
    protected $table = 'pendaftaran_siswas';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'diproses_at'   => 'datetime',
        'setuju'        => 'boolean',
    ];

    public const MENUNGGU = 'menunggu';
    public const DITERIMA = 'diterima';
    public const DITOLAK  = 'ditolak';

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
     * Terima pendaftar: buat akun di tabel users (role "siswa") lalu hubungkan.
     * Mengembalikan kode akses (password awal, plain) untuk dikirim lewat WhatsApp.
     *
     * Jika sudah punya logika lama (Student::updateOrCreate + NIS otomatis),
     * panggil di dalam transaksi ini.
     */
    public function terima(?string $catatan = null): string
    {
        $kode = Str::upper(Str::random(8));

        DB::transaction(function () use ($kode, $catatan) {
            $user = User::firstOrNew(['email' => $this->emailLogin()]);
            $user->forceFill([
                'name'     => $this->nama_lengkap,
                'password' => Hash::make($kode),
                'role'     => 'siswa',
            ])->save();

            $this->update([
                'status'        => self::DITERIMA,
                'catatan_admin' => $catatan,
                'diproses_at'   => now(),
                'user_id'       => $user->id,
            ]);
        });

        return $kode;
    }

    public function tolak(string $catatan): void
    {
        $this->update([
            'status'        => self::DITOLAK,
            'catatan_admin' => $catatan,
            'diproses_at'   => now(),
        ]);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFotoUrlAttribute(): ?string
    {
        return $this->berkas_foto ? asset('storage/' . $this->berkas_foto) : null;
    }

    public function getLabelStatusAttribute(): string
    {
        return match ($this->status) {
            self::DITERIMA => 'Diterima',
            self::DITOLAK  => 'Ditolak',
            default        => 'Menunggu Verifikasi',
        };
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
