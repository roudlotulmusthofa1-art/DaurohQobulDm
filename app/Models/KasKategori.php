<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Kategori Buku Kas (Keuangan > Buku Kas > Kelola Kategori) */
class KasKategori extends Model
{
    protected $table = 'kas_kategori';

    protected $fillable = ['nama', 'tipe'];

    public function transaksi()
    {
        return $this->hasMany(KasTransaksi::class, 'kas_kategori_id');
    }
}
