<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Tanda suka album galeri: satu per browser (kunci = kode acak dari cookie). */
class GaleriSuka extends Model
{
    protected $connection = 'pusat';

    protected $table = 'galeri_suka';

    protected $fillable = ['kiriman_id', 'kunci'];
}
