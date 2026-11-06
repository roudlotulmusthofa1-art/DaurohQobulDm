<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Support\JenisPenilaian;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Admin > Penilaian.
 * - Kelas & Pelajaran : Nilai Harian / Ujian Lisan / Ujian (alurnya menyusul)
 * - Pelanggaran       : rekap per siswa + semua laporan asatid + Terlambat/Alfa dari absensi
 * - Pencapaian        : rekap per siswa + semua laporan asatid
 * Pelanggaran & Pencapaian memakai halaman yang sama (admin/penilaian/catatan.blade.php),
 * pengaturannya di App\Support\JenisPenilaian.
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

    /** GET /admin/penilaian/pelanggaran  &  /admin/penilaian/pencapaian */
    public function catatan(Request $request, string $tipe)
    {
        $atur = JenisPenilaian::ambil($tipe);

        $f = $request->validate([
            'dari'   => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
        ]);
        $dari = $f['dari'] ?? null;
        $sampai = $f['sampai'] ?? null;

        if ($atur['dari_absensi']) {
            Absensi::tutupYangLewat(); // Alfa dari absensi kegiatan yang waktunya habis ikut terhitung
        }

        return view('admin.penilaian.catatan', [
            'atur'   => $atur,
            'baris'  => JenisPenilaian::semuaBaris($tipe, $dari, $sampai),
            'jenis'  => JenisPenilaian::daftarJenis($tipe, false),
            'dari'   => $dari,
            'sampai' => $sampai,
        ]);
    }

    /** DELETE /admin/penilaian/{tipe}/{id} — hapus laporan asatid yang salah */
    public function hapus(Request $request, string $id): JsonResponse
    {
        $model = JenisPenilaian::ambil($request->route('tipe'))['model'];
        $model::findOrFail($id)->delete();

        return response()->json(['message' => __('Laporan dihapus.')]);
    }
}
