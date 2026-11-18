<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Tanda suka berita: satu per browser (kunci = kode acak dari cookie). */
class BeritaSuka extends Model
{
    protected $connection = 'pusat';

    protected $table = 'berita_suka';

    protected $fillable = ['berita_id', 'kunci'];
}
