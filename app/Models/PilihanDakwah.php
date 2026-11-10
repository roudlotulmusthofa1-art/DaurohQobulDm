<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Penilaian Dakwah: pilihan yang ditulis admin sendiri (tombol "+").
 * Contoh: jenis nilai "Sempurna", "Baik"; waktu khitoba' "Ba'da Subuh".
 */
class PilihanDakwah extends Model
{
    /** Kelompok => label. */
    public const KELOMPOK = [
        'nilai_khitobah' => "Jenis nilai Khitoba'",
        'waktu_khitobah' => "Waktu Khitoba'",
        'nilai_khuruj'   => 'Jenis nilai Khurujud Dakwah',
    ];

    protected $table = 'pilihan_dakwah';

    protected $fillable = ['kelompok', 'nama', 'urutan'];

    /** Semua pilihan untuk Vue: ['nilai_khitobah' => [{id, nama}], ...] */
    public static function untukVue(): array
    {
        $semua = self::orderBy('urutan')->orderBy('id')->get(['id', 'kelompok', 'nama']);

        return collect(array_keys(self::KELOMPOK))
            ->mapWithKeys(fn ($k) => [$k => $semua->where('kelompok', $k)->map(fn ($p) => ['id' => $p->id, 'nama' => $p->nama])->values()->all()])
            ->all();
    }
}
