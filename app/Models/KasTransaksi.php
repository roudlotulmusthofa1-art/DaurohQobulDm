<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Satu transaksi kas (Keuangan > Buku Kas). tipe: masuk | keluar, jumlah dalam rupiah. */
class KasTransaksi extends Model
{
    protected $table = 'kas_transaksi';

    public const TIPE = ['masuk' => 'Masuk', 'keluar' => 'Keluar'];

    protected $fillable = ['tanggal', 'tipe', 'kas_kategori_id', 'deskripsi', 'pihak', 'jumlah', 'bukti', 'user_id'];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah'  => 'integer',
    ];

    public function kategori()
    {
        return $this->belongsTo(KasKategori::class, 'kas_kategori_id');
    }

    /** Total masuk & keluar dari sebuah query: ['masuk' => 0, 'keluar' => 0] */
    public static function total($query = null): array
    {
        $baris = ($query ?? self::query())->clone()->reorder()
            ->selectRaw('tipe, SUM(jumlah) AS n')->groupBy('tipe')->pluck('n', 'tipe');

        return ['masuk' => (int) ($baris['masuk'] ?? 0), 'keluar' => (int) ($baris['keluar'] ?? 0)];
    }

    /** Saldo semua waktu = total masuk - total keluar */
    public static function saldo(): int
    {
        $t = self::total();

        return $t['masuk'] - $t['keluar'];
    }
}
