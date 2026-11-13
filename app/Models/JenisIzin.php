<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Penilaian > Perizinan: nama jenis izin yang ditulis admin sendiri (mis. Pulang, Berobat). */
class JenisIzin extends Model
{
    protected $table = 'jenis_izin';

    protected $fillable = ['nama', 'urutan'];

    /** [{id, nama}] untuk Vue */
    public static function untukVue(): array
    {
        return static::orderBy('urutan')->orderBy('id')->get(['id', 'nama'])
            ->map(fn ($j) => ['id' => $j->id, 'nama' => $j->nama])->all();
    }
}
