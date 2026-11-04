<?php

namespace App\Models;

use App\Models\Concerns\ResetPasswordAkun;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Pendaftaran admin.
 * Sama seperti PendaftaranAsatid, tapi datanya lebih sedikit dan akun yang dibuat ber-role "admin".
 */
class PendaftaranAdmin extends Model
{
    use ResetPasswordAkun; // resetPassword(): tombol Reset Password di halaman detail

    /** Dipakai bersama semua sesi -> selalu di database utama */
    protected $connection = 'pusat';

    protected $table = 'pendaftaran_admin';

    protected $guarded = ['id'];

    protected $hidden = ['password'];

    protected $casts = [
        'diproses_at' => 'datetime',
    ];

    public const MENUNGGU = 'menunggu';
    public const DITERIMA = 'diterima';
    public const DITOLAK  = 'ditolak';

    /** Role akun di tabel users */
    public const ROLE = 'admin';

    /** Nomor pendaftaran: ADM20260001 (urut per tahun). Panggil di dalam DB::connection('pusat')->transaction(). */
    public static function buatNomor(): string
    {
        $prefix = 'ADM' . now()->format('Y');

        $terakhir = static::where('nomor_pendaftaran', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('nomor_pendaftaran')
            ->value('nomor_pendaftaran');

        $urut = $terakhir ? ((int) substr($terakhir, -4)) + 1 : 1;

        return $prefix . str_pad($urut, 4, '0', STR_PAD_LEFT);
    }

    /** Kode rahasia di link pendaftaran admin. Disimpan di tabel pengaturan. */
    public static function kodeLink(): string
    {
        return Pengaturan::ambilPusat('admin_daftar_kode') ?: static::buatKodeLinkBaru();
    }

    /** Buat kode link baru (link lama otomatis tidak berlaku). */
    public static function buatKodeLinkBaru(): string
    {
        $kode = Str::lower(Str::random(32));
        Pengaturan::simpanPusat('admin_daftar_kode', $kode);

        return $kode;
    }

    /** Terima: buat/ubah akun di tabel users dengan role admin. */
    public function terima(?string $catatan = null): void
    {
        DB::connection('pusat')->transaction(function () use ($catatan) {
            $user = User::firstOrNew(['email' => $this->email]);

            $user->forceFill([
                'name'     => $this->nama_lengkap,
                'password' => $this->password, // sudah ter-hash saat mendaftar, JANGAN di-Hash::make lagi
                'role'     => self::ROLE,
            ])->save();

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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Pencarian: nama, nama Arab, nomor pendaftaran, email, no. WhatsApp */
    public function scopeCari(Builder $q, ?string $kata): Builder
    {
        return $q->when($kata, fn ($q) => $q->where(fn ($w) => $w
            ->where('nama_lengkap', 'like', "%{$kata}%")
            ->orWhere('nama_lengkap_arab', 'like', "%{$kata}%")
            ->orWhere('nomor_pendaftaran', 'like', "%{$kata}%")
            ->orWhere('email', 'like', "%{$kata}%")
            ->orWhere('no_hp', 'like', "%{$kata}%")));
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
}
