<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Akademik > Munaqosah: satu kolom isian yang ditulis admin di tombol Pengaturan
 * (Nilai Ilmu / Suluk / Catatan Mandub).
 */
class MunaqosahKolom extends Model
{
    /** Bagian => label tombol pengaturan / judul panel */
    public const BAGIAN = [
        'ilmu'   => 'Nilai Ilmu',
        'suluk'  => 'Nilai Suluk',
        'mandub' => 'Catatan Mandub',
    ];

    /** Jenis isian => label */
    public const JENIS = [
        'teks'         => 'Isian pendek',
        'teks_panjang' => 'Isian panjang',
        'dropdown'     => 'Dropdown (pilih satu)',
        'centang'      => 'Centang (boleh beberapa)',
    ];

    /** Jenis yang memakai daftar pilihan */
    public const BERPILIHAN = ['dropdown', 'centang'];

    protected $table = 'munaqosah_kolom';

    protected $fillable = ['bagian', 'label', 'jenis', 'pilihan', 'urutan'];

    protected $casts = [
        'pilihan' => 'array',
    ];

    /** Semua kolom dikelompokkan per bagian: ['ilmu' => [...], 'suluk' => [...], 'mandub' => [...]] */
    public static function perBagian(): array
    {
        $semua = self::orderBy('urutan')->orderBy('id')->get();

        return collect(self::BAGIAN)->keys()->mapWithKeys(fn ($b) => [
            $b => $semua->where('bagian', $b)->map(fn (self $k) => [
                'id'      => $k->id,
                'label'   => $k->label,
                'jenis'   => $k->jenis,
                'pilihan' => array_values($k->pilihan ?? []),
            ])->values()->all(),
        ])->all();
    }
}
