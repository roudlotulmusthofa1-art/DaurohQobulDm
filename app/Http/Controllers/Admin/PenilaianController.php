<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\BulanAjaran;
use App\Models\CatatanSiswa;
use App\Models\Kelas;
use App\Models\Kitab;
use App\Models\NilaiHarian;
use App\Support\DataCatatan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Admin > Penilaian.
 * - Kelas & Pelajaran : Nilai Harian (6 penilaian dari asatid, lihat App\Models\NilaiHarian) / Ujian Lisan / Ujian (alurnya menyusul)
 * - Catatan           : catatan Pencapaian & Pelanggaran dari asatid
 *                       (+ Terlambat/Alfa dari absensi sebagai pelanggaran), lihat App\Support\DataCatatan
 */
class PenilaianController extends Controller
{
    public const BAGIAN_KELAS = ['harian', 'lisan', 'ujian'];

    /**
     * GET /admin/penilaian/kelas?tab=harian|lisan|ujian
     * Nilai Harian disaring: &kelas_id=&kitab_id=&bulan_id= (kosong = bulan ajaran aktif, "semua" = semua bulan);
     * satu santri + kitab + bulan ajaran = satu nilai. Rekap per santri dihitung Vue.
     */
    public function kelas(Request $request)
    {
        $tab = in_array($request->query('tab'), self::BAGIAN_KELAS, true) ? $request->query('tab') : 'harian';

        $f = $request->validate([
            'kelas_id' => ['nullable', 'integer'],
            'kitab_id' => ['nullable', 'integer'],
            'bulan_id' => ['nullable', 'regex:/^(\d+|semua)$/'],
        ]);
        $bulanId = $f['bulan_id'] ?? (string) (BulanAjaran::aktif()?->id ?? 'semua');

        $nilai = NilaiHarian::with(['siswa', 'kelas:id,nama', 'kitab:id,nama', 'bulanAjaran:id,nama', 'penilai:id,name'])
            ->when($f['kelas_id'] ?? null, fn ($q, $v) => $q->where('kelas_id', $v))
            ->when($f['kitab_id'] ?? null, fn ($q, $v) => $q->where('kitab_id', $v))
            ->when($bulanId !== 'semua', fn ($q) => $q->where('bulan_ajaran_id', $bulanId))
            ->orderByDesc('tanggal')->orderByDesc('id')
            ->get()
            ->map(fn (NilaiHarian $n) => $n->baris())->values();

        return view('admin.penilaian.kelas', [
            'tab'       => $tab,
            'nilai'     => $nilai,
            'aspek'     => NilaiHarian::daftarAspek(),
            'kelasList' => Kelas::ordered()->get(['id', 'nama']),
            'kitabList' => Kitab::orderBy('nama')->get(['id', 'nama']),
            'bulanList' => BulanAjaran::with('tahunAjaran:id,nama')->orderByDesc('tahun_ajaran_id')->orderByDesc('urutan')
                ->get(['id', 'tahun_ajaran_id', 'nama', 'urutan', 'is_active']),
            'saring'    => [
                'kelas_id' => $f['kelas_id'] ?? null,
                'kitab_id' => $f['kitab_id'] ?? null,
                'bulan_id' => $bulanId,
            ],
            'saringDiubah' => (bool) array_filter($f),
        ]);
    }

    /** DELETE /admin/penilaian/nilai-harian/{nilai} — hapus nilai harian yang salah */
    public function hapusNilaiHarian(NilaiHarian $nilai): JsonResponse
    {
        $nilai->delete();

        return response()->json(['message' => __('Nilai harian dihapus.')]);
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
