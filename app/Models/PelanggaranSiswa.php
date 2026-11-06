<?php

namespace App\Models;

/** Laporan pelanggaran satu siswa (dari asatid). Lihat CatatanPenilaian. */
class PelanggaranSiswa extends CatatanPenilaian
{
    public const KOLOM_JENIS = 'jenis_pelanggaran_id';

    public const MODEL_JENIS = JenisPelanggaran::class;

    protected $table = 'pelanggaran_siswa';
}
