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

    /** Halaman pertama setelah login, sesuai role */
    public function halamanAwal(): string
    {
        return match ($this->role) {
            'asatid' => route('asatid.dashboard'),
            'siswa'  => route('siswa.profil'),
            default  => route('admin.dashboard'),
        };
    }
}
