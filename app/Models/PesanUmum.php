<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Pesan pengunjung landing page untuk admin (tombol "Hubungi Admin").
 * Disimpan di database utama (koneksi "pusat"). Lihat migrasi create_pesan_umum_table.
 */
class PesanUmum extends Model
{
    protected $connection = 'pusat';

    protected $table = 'pesan_umum';

    protected $fillable = ['nama', 'kontak', 'isi', 'ip', 'dibaca_at'];

    protected $casts = ['dibaca_at' => 'datetime'];

    public function scopeBelumDibaca($q)
    {
        return $q->whereNull('dibaca_at');
    }

    /** Kontak berupa email? (untuk tombol balas: email atau WhatsApp) */
    public function getKontakEmailAttribute(): bool
    {
        return filter_var($this->kontak, FILTER_VALIDATE_EMAIL) !== false;
    }

    /** Tautan balas: mailto: untuk email, wa.me untuk nomor HP (08xx diubah ke 628xx) */
    public function getTautanBalasAttribute(): ?string
    {
        if ($this->kontak_email) {
            return 'mailto:' . $this->kontak;
        }
        $angka = preg_replace('/\D/', '', $this->kontak);
        if (strlen($angka) < 8) {
            return null;
        }

        return 'https://wa.me/' . (str_starts_with($angka, '0') ? '62' . substr($angka, 1) : $angka);
    }
}
