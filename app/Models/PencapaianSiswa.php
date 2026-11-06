<?php

namespace App\Models;

/** Laporan pencapaian (prestasi) satu siswa (dari asatid). Lihat CatatanPenilaian. */
class PencapaianSiswa extends CatatanPenilaian
{
    public const KOLOM_JENIS = 'jenis_pencapaian_id';

    public const MODEL_JENIS = JenisPencapaian::class;

    protected $table = 'pencapaian_siswa';
}
