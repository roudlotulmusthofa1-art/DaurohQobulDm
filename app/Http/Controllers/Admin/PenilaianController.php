<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\CatatanSiswa;
use App\Support\DataCatatan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Admin > Penilaian.
 * - Kelas & Pelajaran : Nilai Harian / Ujian Lisan / Ujian (alurnya menyusul)
 * - Catatan           : catatan Pencapaian & Pelanggaran dari asatid
 *                       (+ Terlambat/Alfa dari absensi sebagai pelanggaran), lihat App\Support\DataCatatan
 */
class PenilaianController extends Controller
{
    public const BAGIAN_KELAS = ['harian', 'lisan', 'ujian'];

    /** GET /admin/penilaian/kelas?tab=harian|lisan|ujian */
    public function kelas(Request $request)
    {
        $tab = in_array($request->query('tab'), self::BAGIAN_KELAS, true) ? $request->query('tab') : 'harian';

        return view('admin.penilaian.kelas', ['tab' => $tab]);
    }

    /** GET /admin/penilaian/catatan?jenis=pencapaian|pelanggaran&dari=&sampai= */
    public function catatan(Request $request)
    {
        $f = $request->validate([
            'dari'   => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
        ]);
        $jenis = array_key_exists((string) $request->query('jenis'), CatatanSiswa::JENIS) ? $request->query('jenis') : '';

        Absensi::tutupYangLewat(); // Alfa dari absensi kegiatan yang waktunya habis ikut terhitung

        return view('admin.penilaian.catatan', [
            'baris'  => DataCatatan::semuaBaris($f['dari'] ?? null, $f['sampai'] ?? null),
            'jenis'  => $jenis,
            'dari'   => $f['dari'] ?? null,
            'sampai' => $f['sampai'] ?? null,
        ]);
    }

    /** DELETE /admin/penilaian/catatan/{catatan} — hapus catatan asatid yang salah */
    public function hapus(CatatanSiswa $catatan): JsonResponse
    {
        $catatan->delete();

        return response()->json(['message' => __('Catatan dihapus.')]);
    }
}
