<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /** Akun admin & asatid dipakai bersama semua sesi: disalin otomatis (lihat App\Support\AkunBersama) */
    protected static function booted(): void
    {
        static::creating(fn (User $u) => \App\Support\AkunBersama::pesanId($u));
        static::updating(fn (User $u) => \App\Support\AkunBersama::cegahUbahPeran($u));
        static::saved(fn (User $u) => \App\Support\AkunBersama::sebarkan($u));
        static::deleted(fn (User $u) => \App\Support\AkunBersama::hapus($u));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'aktif' => 'boolean', // false = tidak bisa masuk (Sistem > Daftar User)
        ];
    }
    public function pendaftaran()
    {
        return $this->hasOne(\App\Models\PendaftaranSiswa::class);
    }

    /** Data pendaftaran asatid milik akun ini (kalau role-nya asatid) */
    public function pendaftaranAsatid()
    {
        return $this->hasOne(\App\Models\PendaftaranAsatid::class);
    }

    /**
     * Gambar avatar: foto profil (Pengaturan > Profil) bila ada,
     * kalau belum ada pakai gambar inisial nama dengan warna $latar/$huruf (hex tanpa #).
     */
    public function avatarUrl(string $latar = '0b332c', string $huruf = 'fff'): string
    {
        return $this->foto
            ? \Illuminate\Support\Facades\Storage::disk('bersama')->url($this->foto)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->name ?? 'Admin') . "&background={$latar}&color={$huruf}&size=128";
    }

    /** Halaman pertama setelah login, sesuai role */
    public function halamanAwal(): string
    {
        return match ($this->role) {
            'asatid' => route('asatid.dashboard'),
            'siswa'  => route('siswa.dashboard'),
            default  => route('admin.dashboard'),
        };
    }
}
